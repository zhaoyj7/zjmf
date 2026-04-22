const template = document.getElementsByClassName("common_config")[0];
Vue.prototype.lang = Object.assign(window.lang, window.module_lang);
new Vue({
  components: {
    comConfig,
    comTreeSelect,
    comRegionalMap
  },
  data () {
    return {
      host: location.origin,
      id: "",
      tabs: "duration", // duration,calc,data_center,store,limit,system,recommend,other
      hover: true,
      tableLayout: false,
      gpuModel: false,
      delVisible: false,
      loading: false,
      currency_prefix:
        JSON.parse(localStorage.getItem("common_set")).currency_prefix || "¥",
      currency_suffix:
        JSON.parse(localStorage.getItem("common_set")).currency_suffix || "",
      edition: JSON.parse(localStorage.getItem("common_set")).edition || 0,
      optType: "add", // 新增/编辑
      comTitle: "",
      delTit: "",
      delType: "",
      delId: "",
      submitLoading: false,
      /* 周期 */
      cycleData: [],
      dataModel: false,
      cycleModel: false,
      cycleForm: {
        product_id: "",
        name: "",
        num: "",
        unit: "month",
        price_factor: null,
        price: null,
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
      cycleColumns: [
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
          colKey: "is_default",
          title: `${lang.default}${lang.cycle}`,
          ellipsis: true,
        },
        {
          colKey: "price_factor",
          title: lang.price_factor,
          ellipsis: true,
        },
        {
          colKey: "price",
          title: lang.cycle_price,
          ellipsis: true,
          className: "price-type-cell"
        },
        {
          colKey: "ratio",
          title: lang.cycle_ratio,
          ellipsis: true,
          align: "center"
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
            validator: (val) => val?.length <= 10,
            message: lang.verify8 + "1-10",
            type: "warning",
          },
        ],
        num: [
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
            validator: (val) => val > 0 && val <= 99999,
            message: lang.cycle_time + "1-99999",
            type: "warning",
          },
        ],
        // 系统相关
        image_group_id: [
          {
            required: true,
            message: lang.select + lang.system_classify,
            type: "error",
          },
        ],
        rel_image_id: [
          {
            required: true,
            message: lang.input + lang.opt_system + "ID",
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + lang.verify16,
            type: "warning",
          },
        ],
        price: [
          {
            required: true,
            message: lang.input + lang.box_title34,
            type: "error",
          },
          {
            pattern: /^\d+(\.\d{0,2})?$/,
            message: lang.verify12,
            type: "warning",
          },
          {
            validator: (val) => val >= 0,
            message: lang.verify12,
            type: "warning",
          },
        ],
        icon: [
          {
            required: true,
            message: lang.select + lang.mf_icon,
            type: "error",
            trigger: "change",
          },
        ],
      },
      /* 操作系统 */
      systemGroup: [],
      systemList: [],
      selectedRowKeys: [],
      systemParams: {
        product_id: "",
        page: 1,
        limit: 1000,
        image_group_id: "",
        keywords: "",
      },
      systemModel: false,
      createSystem: {
        // 添加操作系统表单
        image_group_id: "",
        name: "",
        charge: 0,
        price: "",
        enable: 0,
        rel_image_id: "",
      },
      systemColumns: [
        {
          colKey: "drag", // 列拖拽排序必要参数
          cell: "drag",
          width: 50,
        },
        // 套餐表格
        {
          colKey: "row-select",
          type: "multiple",
          width: 30,
        },
        {
          colKey: "id",
          title: lang.order_index,
          width: 100,
          ellipsis: true,
        },
        {
          colKey: "image_group_name",
          title: lang.system_classify,
          width: 200,
          ellipsis: true,
        },
        {
          colKey: "name",
          title: lang.system_name,
          ellipsis: true,
        },
        {
          colKey: "charge",
          title: lang.mf_charge,
          width: 200,
        },
        {
          colKey: "price",
          title: lang.box_title34,
          align: "right",
          minWidth: 120
        },
        {
          colKey: "enable",
          title: lang.mf_enable,
          width: 120,
        },
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",
          width: 120,
        },
      ],
      groupColumns: [
        // 套餐表格
        {
          // 列拖拽排序必要参数
          colKey: "drag",
          width: 20,
          className: "drag-icon",
        },
        {
          colKey: "image_group_name",
          title: lang.system_classify,
          ellipsis: true,
          className: "group-column",
        },
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",

          width: 120,
        },
      ],
      // 操作系统图标
      iconList: [
        "Windows",
        "CentOS",
        "Ubuntu",
        "Debian",
        "ESXi",
        "XenServer",
        "FreeBSD",
        "Fedora",
        "其他",
        "ArchLinux",
        "Rocky",
        "OpenEuler",
        "AlmaLinux",
      ],
      iconSelecet: [],
      classModel: false,
      classParams: {
        id: "",
        name: "",
        icon: "",
      },
      popupProps: {
        overlayClassName: `custom-select`,
        overlayInnerStyle: (trigger) => ({ width: `${trigger.offsetWidth}px` }),
      },
      /* 其他设置 */
      otherForm: {
        product_id: "",
        host_prefix: "",
        host_length: "",
        //  ipv6_num: "",
        manual_manage: false,
        nat_acl_limit: "",
        nat_web_limit: "",
        niccard: 0,
        cpu_model: 0,
        node_priority: 1,
        ip_mac_bind: 0,
        default_one_ipv4: 0,
        support_ssh_key: 0,
        rand_ssh_port: 0,
        backup_enable: 0,
        snap_enable: 0,
        reinstall_sms_verify: 0,
        reset_password_sms_verify: 0,
        simulate_physical_machine_enable: 0,
        snap_data: [],
        backup_data: [],
        resource_package: [],
        is_agent: "",
        type: "", // host: kvm专业版 lightHost: kvm轻量版 hyperv ：Hyper-V
        disk_limit_switch: 0,
        disk_limit_num: null,
        free_disk_switch: 0,
        free_disk_size: null,
        free_disk_type: "",
        only_sale_recommend_config: 0,
        default_nat_acl: false,
        default_nat_web: false,
        /* ssh端口 */
        rand_ssh_port_start: null,
        rand_ssh_port_end: null,
        rand_ssh_port_windows: null,
        rand_ssh_port_linux: null,
        duration_id: [],
        // 防火墙
        sync_firewall_rule: 0,
        order_default_defence: "", // 空的时候默认传0
        // 密码长度
        custom_rand_password_rule: 0,
        default_password_length: null,
        // 磁盘步长
        disk_range_limit_switch: 0,
        disk_range_limit: null,
      },
      versionArr: [
        { value: "host", label: lang.kvm_major },
        { value: "lightHost", label: lang.kvm_light },
        { value: "hyperv", label: "Hyper-V" },
      ],
      rulesList: [
        // 平衡规则
        { value: 1, label: lang.mf_rule1 },
        { value: 2, label: lang.mf_rule2 },
        { value: 3, label: lang.mf_rule3 },
        { value: 4, label: lang.mf_rule4 },
      ],
      dataRules: {
        data_center_id: [
          {
            required: true,
            message: `${lang.select}${lang.area}`,
            type: "error",
          },
        ],
        line_id: [
          {
            required: true,
            message: `${lang.select}${lang.line_name}`,
            type: "error",
          },
        ],
        flow: [
          {
            required: true,
            message: `${lang.input}${lang.cloud_flow}`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "0-9999999" + lang.verify1,
            type: "warning",
          },
          {
            validator: (val) => val >= 0 && val <= 9999999,
            message: lang.input + "0-9999999" + lang.verify1,
            type: "warning",
          },
        ],
        host_prefix: [
          {
            required: true,
            message: `${lang.input}${lang.host_prefix}`,
            type: "error",
          },
          {
            pattern: /^[A-Za-z][a-zA-Z0-9_.]{0,9}$/,
            message: lang.verify8 + "1-10",
            type: "warning",
          },
        ],
        host_length: [
          {
            required: true,
            message: `${lang.input}${lang.mf_tip2}`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.mf_tip2,
            type: "warning",
          },
        ],
        country_id: [
          {
            required: true,
            message: lang.select + lang.country_area,
            type: "error",
          },
        ],
        city: [
          { required: true, message: lang.select + lang.city, type: "error" },
        ],
        cloud_config: [
          { required: true, message: lang.select + lang.city, type: "error" },
        ],
        cloud_config_id: [
          { required: true, message: lang.input + "ID", type: "error" },
        ],
        area: [
          {
            required: true,
            message: `${lang.input}${lang.area}${lang.nickname}`,
            type: "error",
          },
        ],
        name: [
          {
            required: true,
            message: `${lang.input}${lang.box_label23}`,
            type: "error",
          },
        ],
        description: [
          {
            required: true,
            message: `${lang.input}${lang.description}`,
            type: "error",
          },
        ],
        order: [
          {
            required: true,
            message: `${lang.input}${lang.sort}ID`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.verify7,
            type: "warning",
          },
          {
            validator: (val) => val >= 0,
            message: lang.verify7,
            type: "warning",
          },
        ],
        cpu: [
          { required: true, message: `${lang.input}CPU`, type: "error" },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-512" + lang.verify1,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 512,
            message: lang.input + "1-512" + lang.verify1,
            type: "warning",
          },
        ],
        gpu_num: [
          { required: false },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "0-100" + lang.verify1,
            type: "warning",
          },
          {
            validator: (val) => val >= 0 && val <= 100,
            message: lang.input + "0-100" + lang.verify1,
            type: "warning",
          },
        ],
        gpu_name: [
          {
            required: true,
            message: `${lang.input}GPU${lang.box_title46}`,
            type: "error",
          },
        ],
        memory: [
          {
            required: true,
            message: `${lang.input}${lang.memory}`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-512" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 512,
            message: lang.input + "1-512" + lang.verify2,
            type: "warning",
          },
        ],
        system_disk_size: [
          {
            required: true,
            message: `${lang.input}${lang.system_disk_size}`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-1048576" + lang.verify1,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 1048576,
            message: lang.input + "1-1048576" + lang.verify1,
            type: "warning",
          },
        ],
        data_disk_size: [
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-1048576" + lang.verify1,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 1048576,
            message: lang.input + "1-1048576" + lang.verify1,
            type: "warning",
          },
        ],
        network_type: [
          {
            required: true,
            message: lang.select + lang.net_type,
            type: "error",
          },
        ],
        bw: [
          { required: true, message: `${lang.input}${lang.bw}`, type: "error" },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-30000" + lang.verify1,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 30000,
            message: lang.input + "1-30000" + lang.verify1,
            type: "warning",
          },
        ],
        peak_defence: [
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-9999999" + lang.verify1,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 9999999,
            message: lang.input + "1-9999999" + lang.verify1,
            type: "warning",
          },
        ],
        min_memory: [
          {
            required: true,
            message: `${lang.input}${lang.memory}`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-512" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 512,
            message: lang.input + "1-512" + lang.verify2,
            type: "warning",
          },
        ],
        max_memory: [
          {
            required: true,
            message: `${lang.input}${lang.memory}`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-512" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 512,
            message: lang.input + "1-512" + lang.verify2,
            type: "warning",
          },
        ],
        line_id: [
          {
            required: true,
            message: `${lang.select}${lang.bw_line}`,
            type: "error",
          },
        ],
        min_bw: [
          {
            required: true,
            message: `${lang.input}${lang.min_value}`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-30000" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 30000,
            message: lang.input + "1-30000" + lang.verify2,
            type: "warning",
          },
        ],
        max_bw: [
          {
            required: true,
            message: `${lang.input}${lang.max_value}`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-30000" + lang.verify2,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 30000,
            message: lang.input + "1-30000" + lang.verify2,
            type: "warning",
          },
        ],
        price: [
          {
            pattern: /^\d+(\.\d{0,2})?$/,
            message: lang.input + lang.money,
            type: "warning",
          },
          {
            validator: (val) => val >= 0 && val <= 9999999,
            message: lang.verify12,
            type: "warning",
          },
        ],
        image_group_id: [
          {
            required: true,
            message: `${lang.select}${lang.system_classify}`,
            type: "error",
          },
        ],
        image_id: [
          {
            required: true,
            message: `${lang.select}${lang.system_name}`,
            type: "error",
          },
        ],
        rand_ssh_port_start: [
          {
            required: true,
            message: `${lang.input}${lang.start_port}`,
            type: "error",
          },
        ],
        rand_ssh_port_end: [
          {
            required: true,
            message: `${lang.input}${lang.end_port}`,
            type: "error",
          },
        ],
        rand_ssh_port_windows: [
          {
            required: true,
            message: `${lang.input}`,
            type: "error",
          },
        ],
        rand_ssh_port_linux: [
          {
            required: true,
            message: `${lang.input}`,
            type: "error",
          },
        ],
        in_bw: [
          {
            required: true,
            message: `${lang.input}${lang.inflow_bw}`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-30000" + lang.verify1,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 30000,
            message: lang.input + "1-30000" + lang.verify1,
            type: "warning",
          },
        ],
        traffic_type: [
          {
            required: true,
            message: `${lang.select}`,
            type: "error",
          },
        ],
        default_password_length: [
          {
            required: true,
            message: lang.mf_demand_tip22,
            type: "error",
          },
          {
            pattern: /^(?:[6-9]|1[0-9]|20)$/,
            message: lang.mf_demand_tip22,
            type: "warning",
          }
        ]
      },
      ontralRules: {
        cycle_num: [
          {
            required: true,
            message: `${lang.input}${lang.ontrial_text5}`,
            type: "error",
          },
        ],
        max: [
          {
            required: true,
            message: `${lang.input}${lang.ontrial_text17}`,
            type: "error",
          },
        ],
      },
      backupColumns: [
        // 备份表格
        {
          colKey: "id",
          title: lang.order_index,
          width: 160,
        },
        {
          colKey: "num",
          title: lang.allow_back_num,
          ellipsis: true,
          minWidth: 180,
        },
        {
          colKey: "price",
          title: lang.min_cycle_price,
          className: "back-price",
        },
      ],
      snapColumns: [
        // 快照表格
        {
          colKey: "id",
          title: lang.order_index,
          width: 160,
        },
        {
          colKey: "num",
          title: lang.allow_snap_num,
          minWidth: 180,
          ellipsis: true,
        },
        {
          colKey: "price",
          title: lang.min_cycle_price,
          className: "back-price",
        },
      ],
      resourceColumns: [
        // 资源包
        {
          colKey: "id",
          title: lang.order_index,
          width: 160,
        },
        {
          colKey: "rid",
          title: `${lang.resource_package}ID`,
          width: 180,
          ellipsis: true,
        },
        {
          colKey: "name",
          title: `${lang.resource_package}${lang.nickname}`,
          className: "back-price",
        },
      ],
      backList: [],
      snapList: [],
      resourceList: [],
      backLoading: false,
      snapLoading: false,
      backAllStatus: false,
      /* 计算配置 */
      cpuList: [],
      cpuLoading: false,
      memoryList: [],
      memoryLoading: false,
      memoryType: "", // 内存方式
      cpuColumns: [
        // cpu表格
        {
          colKey: "value",
          title: `CPU（${lang.cores}）`,
        },
        {
          colKey: "price",
          title: lang.box_title34,
          className: "price-column"
        },
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",
          width: 120,
        },
      ],
      memoryColumns: [
        // memory表格
        {
          colKey: "value",
          title: `${lang.memory}（GB）`,
        },
        {
          colKey: "price",
          title: lang.box_title34,
          className: "price-column"
        },
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",
          width: 120,
        },
      ],
      calcType: "", // cpu, memory
      calcForm: {
        // cpu
        product_id: "",
        cpuValue: "", // cpu里面的value， 提交的时候转换
        price: [],
        on_demand_price: "", // 按需价格
        other_config: {
          advanced_cpu: "",
          cpu_limit: "",
          ipv6_num: "",
          disk_type: "",
        },
        // memory
        type: "",
        value: "",
        min_value: "",
        max_value: "",
        step: "",
        memory_unit: "GB",
        // 性能
        read_bytes: "",
        write_bytes: "",
        read_iops: "",
        write_iops: "",
      },
      calcModel: false,
      configType: [
        { value: "radio", label: lang.mf_radio },
        { value: "step", label: lang.mf_step },
        { value: "total", label: lang.mf_total },
      ],
      calcRules: {
        // 计算配置验证
        value: [
          { required: true, message: `${lang.input}${lang.bw}`, type: "error" },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "0-30000" + lang.verify18,
            type: "warning",
          },
          {
            validator: (val) => val >= 0 && val <= 30000,
            message: lang.input + "0-30000" + lang.verify18,
            type: "warning",
          },
        ],
        cpuValue: [
          {
            required: true,
            message: `${lang.input}${lang.mf_cores}`,
            type: "error",
          },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + "1-512" + lang.verify18,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 512,
            message: lang.input + "1-512" + lang.verify18,
            type: "warning",
          },
        ],
        type: [
          {
            required: true,
            message: `${lang.select}${lang.config}${lang.mf_way}`,
            type: "error",
          },
        ],
        price: [
          {
            pattern: /^\d+(\.\d{0,2})?$/,
            message: lang.input + lang.money,
            type: "warning",
          },
          {
            validator: (val) => val >= 0 && val <= 9999999,
            message: lang.verify12,
            type: "warning",
          },
        ],
        min_value: [
          {
            required: true,
            message: `${lang.input}${lang.min_value}`,
            type: "error",
          },
          {
            pattern: /^([1-9][0-9]*)$/,
            message: lang.input + "1~1048576" + lang.verify18,
            type: "warning",
          },
          {
            validator: (val) => val >= 1 && val <= 1048576,
            message: lang.input + "1~1048576" + lang.verify18,
            type: "warning",
          },
        ],
        max_value: [
          {
            required: true,
            message: `${lang.input}${lang.max_value}`,
            type: "error",
          },
          {
            pattern: /^([1-9][0-9]*)$/,
            message: lang.input + "2~1048576" + lang.verify18,
            type: "warning",
          },
          {
            validator: (val) => val >= 2 && val <= 1048576,
            message: lang.input + "2~1048576" + lang.verify18,
            type: "warning",
          },
        ],
        step: [
          {
            required: true,
            message: `${lang.input}${lang.min_step}`,
            type: "error",
          },
          {
            pattern: /^([1-9][0-9]*)$/,
            message: lang.input + lang.verify16,
            type: "warning",
          },
        ],
        read_bytes: [
          { required: true, message: `${lang.input}`, type: "error" },
          { validator: this.checkLimit },
        ],
        write_bytes: [
          { required: true, message: `${lang.input}`, type: "error" },
          { validator: this.checkLimit },
        ],
        read_iops: [
          { required: true, message: `${lang.input}`, type: "error" },
          { validator: this.checkLimit },
        ],
        write_iops: [
          { required: true, message: `${lang.input}`, type: "error" },
          { validator: this.checkLimit },
        ],
        traffic_type: [
          {
            required: true,
            message: `${lang.select}${lang.traffic_type}`,
            type: "error",
          },
        ],
        bill_cycle: [
          {
            required: true,
            message: `${lang.select}${lang.billing_cycle}`,
            type: "error",
          },
        ],
      },
      isAdvance: false, // 是否展开高级配置
      /* 存储配置 */
      systemDisk: [],
      systemLoading: false,
      systemType: "", // 系统盘类型
      dataDisk: [],
      dataLoading: false,
      diskType: "", // 数据盘类型
      systemDiskColumns: [
        {
          colKey: "value",
          title: `${lang.system_disk_size}（GB）`,
          width: 300,
        },
        {
          colKey: "price",
          className: "price-column",
          title: lang.box_title34,
        },
        {
          colKey: "type",
          title: lang.disk,
        },
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",

          width: 120,
        },
      ],
      diskColumns: [],
      store_limit: 0, // 性能限制
      systemLimitList: [],
      systemLimitLoading: false,
      diskLimitLoading: false,
      diskLimitList: [],
      natureColumns: [
        // 性能表格
        {
          colKey: "id",
          title: lang.index_text8,
          width: 100,
          ellipsis: true,
        },
        {
          colKey: "capacity_size",
          title: `${lang.capacity_size}（GB）`,
          width: 200,
        },
        {
          colKey: "read_bytes",
          title: `${lang.random_read}（MB/s）`,
          ellipsis: true,
        },
        {
          colKey: "write_bytes",
          title: `${lang.random_write}（MB/s）`,
          ellipsis: true,
        },
        {
          colKey: "read_iops",
          title: `${lang.read_iops}（IOPS/s）`,
          ellipsis: true,
        },
        {
          colKey: "write_iops",
          title: `${lang.write_iops}（IOPS/s）`,
          ellipsis: true,
        },
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",

          width: 120,
        },
      ],
      disabledWay: false, // 配置方式是否可选
      natureModel: false,
      /* 数据中心 */
      dataList: [],
      dataColumns: [
        {
          colKey: "order",
          title: lang.index_text8,
          width: 100,
          ellipsis: true,
        },
        {
          colKey: "country_name",
          title: lang.country,
          width: 150,
          ellipsis: true,
          className: "country-td",
        },
        {
          colKey: "city",
          title: lang.city,
          width: 150,
          ellipsis: true,
          className: "city-td",
        },
        {
          colKey: "area",
          title: `${lang.area}${lang.nickname}`,
          width: 150,
          ellipsis: true,
          className: "area-td",
        },
        {
          colKey: "gpu_name",
          title: `GPU${lang.box_title46}`,
          className: "area-td",
          width: 250,
          ellipsis: true,
        },
        {
          colKey: "line",
          title: lang.line_name,
          className: "line-td",
          width: 250,
          ellipsis: true,
        },

        {
          colKey: "price",
          title: lang.box_title34,
          className: "line-td gpu",
          ellipsis: true,
          width: 200,
        },
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",
          width: 100,
          className: "line-td",
        },
      ],
      dataForm: {
        // 新建数据中心
        country_id: "",
        city: "",
        area: "",
        cloud_config: "node",
        cloud_config_id: "",
        order: null,
      },
      countryList: [],
      // 配置选项
      dataConfig: [
        { value: "node", lable: lang.node + "ID" },
        { value: "area", lable: lang.area + "ID" },
        { value: "node_group", lable: lang.node_group + "ID" },
      ],
      /* 线路相关 */
      lineType: "", // 新增,编辑线路，新增的时候本地操作，保存一次性提交
      subType: "", // 线路子项类型， line_bw, line_flow, line_defence, line_ip
      lineForm: {
        country_id: "", // 线路国家
        city: "", // 线路城市
        data_center_id: "",
        name: "",
        bill_type: "", // bw, flow
        bw_ip_group: "",
        ipv6_group_id: "",
        defence_ip_group: "",
        ip_enable: 0, // ip开关
        ipv6_enable: 0, // ipv6开关
        defence_enable: 0, // 防护开关
        bw_data: [], // 带宽
        flow_data: [], //流量
        flow_data_on_demand: [],
        defence_data: [], // 防护
        ip_data: [], // ipv4,
        ipv6_data: [],
        flow: "",
        line_id: "",
        link_clone: false,
        // gpu 配置
        gpu_enable: 0,
        gpu_num: "",
        gpu_name: "",
        gpu_data: [],
        due_not_free_gpu: false,
        /* 推荐配置 */
        description: "",
        order: "",
        cpu: "",
        memory: "",
        system_disk_size: "",
        system_disk_type: "",
        data_disk_size: "",
        data_disk_type: "",
        ip_num: "", // 0-2000
        ipv6_num: "",
        bw: "",
        peak_defence: "",
        sync_firewall_rule: 0,
        order_default_defence: "",
        ontrial: 0,
        ontrial_price: null,
        ontrial_stock_control: 0,
        ontrial_qty: null,
        on_demand_price: ""
      },
      bw_ip_show: false, // bw 高级配置
      defence_ip_show: false, // 防护高级配置
      gpuForm: {
        id: "",
        value: "",
        gpu_name: "",
        price: [],
        gpu_data: [],
        area: "",
        city: "",
        on_demand_price: ""
      },
      editGpuId: "",
      subForm: {
        // 线路子项表单
        order: "",
        type: "",
        value: "",
        price: [],
        min_value: "",
        max_value: "",
        step: "",
        other_config: {
          in_bw: "",
          out_bw: "",
          traffic_type: "",
          bill_cycle: "",
          store_id: "",
          advanced_bw: "",
        },
        on_demand_price: ""
      },
      lineModel: false,
      lineRight: false,
      delSubIndex: 0,
      subId: "",
      countrySelect: [], // 国家三级联动
      billType: [
        { value: "bw", label: lang.mf_bw },
        { value: "flow", label: lang.mf_flow },
      ],
      bwColumns: [
        {
          colKey: "fir",
          title: lang.bw,
        },
        {
          colKey: "price",
          title: lang.box_title34,
          className: "price-column",
          width: 180,
          ellipsis: true,
        },
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",
          width: 120,
        },
      ],
      trafficTypes: [
        { value: 1, label: lang.in },
        { value: 2, label: lang.out },
        { value: 3, label: lang.in_out },
      ],
      billingCycle: [
        { value: "month", label: lang.natural_month },
        { value: "last_30days", label: lang.last_30days },
      ],
      /* 推荐配置 */
      calcLineType: "",
      recommendList: [],
      systemDiskType: [],
      dataDiskType: [],
      recommendModel: false,
      recommendColumns: [
        {
          colKey: "order",
          title: lang.order_text68,
          width: 100,
        },
        {
          colKey: "name",
          title: lang.mf_package_name,
          width: 200,
          ellipsis: true,
        },
        {
          colKey: "price",
          title: lang.price,
          ellipsis: true,
          width: 200,
          className: "price-column"
        },
        {
          colKey: "description",
          title: lang.mf_package_des,
          ellipsis: true,
          width: 200,
        },
        {
          colKey: "hidden",
          title: lang.mf_tip40,
          ellipsis: true,
          width: 150,
        },
        {
          colKey: "upgrade_show",
          title: lang.mf_tip46,
          ellipsis: true,
          width: 150,
        },

        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",

          width: 120,
        },
      ],
      networkType: [
        { value: "normal", label: lang.normal_network },
        { value: "vpc", label: lang.vpc_network },
      ],
      batchDelete: false,
      /* 配置限制 */
      ruleLimit: [], // 条件
      resultLimit: [], // 结果
      limitColumns: [
        {
          colKey: "rule",
          title: lang.mf_fill_condition,
          ellipsis: true,
        },
        {
          colKey: "result",
          title: lang.mf_result,
          ellipsis: true,
        },
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",

          width: 120,
        },
      ],
      limitTypeObj: {
        data_center: {
          type: "data_center",
          name: lang.data_center,
          config: { id: [] },
          checked: false,
        },
        cpu: {
          type: "cpu",
          name: "CPU",
          config: { value: [] },
          checked: false,
        },
        memory: {
          type: "memory",
          name: lang.memory,
          config: { min: "", max: "" },
          checked: false,
        },
        bw: {
          type: "bw",
          name: lang.bw,
          config: { min: "", max: "" },
          checked: false,
        },
        flow: {
          type: "flow",
          name: lang.cloud_flow,
          config: { min: "", max: "" },
          checked: false,
        },
        image: {
          type: "image",
          name: lang.opt_system,
          config: { id: [] },
          checked: false,
        },
        ipv4_num: {
          type: "ipv4_num",
          name: `IPv4${lang.auth_num}`,
          config: { min: "", max: "" },
          checked: false,
        },
        system_disk: {
          type: "system_disk",
          name: lang.system_disk_size,
          config: { min: "", max: "" },
          checked: false,
        },
        // data_disk: {
        //   type: "data_disk",
        //   name: lang.data_disk,
        //   config: { min: "", max: "" },
        //   checked: false,
        // },
        // ipv6_num: {
        //   type: "ipv6_num",
        //   name: `IPv6${lang.auth_num}`,
        //   config: { min: "", max: "" },
        //   checked: false,
        // },
        // recommend_config: {
        //   type: "recommend_config",
        //   name: lang.package,
        //   config: { id: [] },
        //   checked: false,
        // },
        // duration: {
        //   type: "duration",
        //   name: lang.cycle,
        //   config: { id: [] },
        //   checked: false,
        // },
      },
      limitData: [],
      limitLoading: false,
      limitType: "",
      limitModel: false,
      originLimitForm: {
        rule: {
          data_center: { opt: "eq", id: [] },
          cpu: { opt: "eq", value: [] },
          memory: { opt: "eq", min: null, max: null, value: [] },
          bw: { opt: "eq", min: null, max: null },
          flow: { opt: "eq", min: null, max: null },
          image: { opt: "eq", id: [] },
          ipv4_num: { opt: "eq", min: null, max: null },
        },
        result: {
          cpu: [{ opt: "eq", value: [] }],
          memory: [{ opt: "eq", min: null, max: null, value: [] }],
          bw: [{ opt: "eq", min: null, max: null }],
          flow: [{ opt: "eq", min: null, max: null }],
          image: [{ opt: "eq", id: [] }],
          ipv4_num: [{ opt: "eq", min: null, max: null }],
          system_disk: [{ opt: "eq", min: null, max: null }],
        },
      },
      limitForm: {},
      limitRules: {
        "rule.data_center.id": [
          {
            required: true,
            message: `${lang.select}${lang.data_center}`,
            type: "error",
          },
        ],
        "rule.cpu.value": [
          { required: true, message: `${lang.select}CPU`, type: "error" },
        ],
        value: [{ required: true, message: `${lang.select}CPU`, type: "error" }],
        "rule.image.id": [
          {
            required: true,
            message: `${lang.select}${lang.opt_system}`,
            type: "error",
          },
        ],
        id: [
          {
            required: true,
            message: `${lang.select}${lang.opt_system}`,
            type: "error",
          },
        ],
      },
      limitMemoryType: "", // 配置限制里面内存的方式
      memory_unit: "",
      showConfirm: false,
      deleteTip: "",
      backNatureColumns: [],
      tempNum: null,
      tempFree: null,
      tempRangeLimit: null,
      isInit: true,
      /* 升降级范围 */
      upgradeModel: false,
      upgradeColumns: [
        {
          colKey: "name",
          title: lang.mf_package_name,
          ellipsis: true,
        },
        {
          colKey: "price",
          title: lang.price,
          ellipsis: true,
        },
        {
          colKey: "range",
          title: lang.demote_range,
          ellipsis: true,
          width: 300,
        },
      ],
      ipType: "", // ipv4, ipv6
      isEn: localStorage.getItem("backLang") === "en-us" ? true : false,
      isAgent: false, // 是否是代理商品
      isShowCycleTip: false,
      multiliTip: "",
      isLocalSystem: false,
      pullOsVisible: false,
      pullOsLoading: false,

      /* 防火墙 */
      hasFirewall: false,
      defenceModel: false,
      defenceForm: {
        id: "",
        sync_firewall_rule: 0,
        order_default_defence: "",
        // 导入规则
        firewall_type: "",
        defence_rule_id: [],
        price: [],
      },
      isEditDefence: false,
      checkRuleloading: false,
      defenceRuleName: "",
      defencePeak: "",
      defenceOrder: "",
      allFirewallRuleType: [], //可选防火墙规则
      allFirewallRule: [], // 当前所选防火墙
      checkedFirewallRule: [], // 已选防火墙规则
      firewallLoading: false,
      importRuleLoading: false,
      checkRuleId: [],
      checkRuleArr: [],
      allFirewallColumns: [
        {
          colKey: "row-select",
          type: "multiple",
          width: 30,
          disabled: ({ row }) => this.disabledRule(row),
        },
        {
          colKey: "defense_peak",
          title: lang.mf_tip55,
          ellipsis: true,
        },
        {
          colKey: "name",
          title: lang.mf_tip52,
          ellipsis: true,
        },
      ],
      recommendDefenceName: "",
      isShowLinePrice: true,
      isFirewallProduct: 0,
      tempSyncSwitch: 0,
      cycleTypeOptions: [
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
      clientLimitOptions: [
        { value: "no", label: lang.ontrial_text7 },
        { value: "new", label: lang.ontrial_text8 },
        { value: "host", label: lang.ontrial_text9 },
      ],
      accountLimitOptions: [
        { value: "email", label: lang.ontrial_text10 },
        { value: "phone", label: lang.ontrial_text11 },
        { value: "certification", label: lang.ontrial_text12 },
      ],
      payOntrialForm: {
        status: 0,
        cycle_type: "hour",
        cycle_num: null,
        client_limit: "no",
        account_limit: [],
        old_client_exclusive: [],
        max: null,
      },
      /* 按需 */
      /* 按需 */
      demandForm: {
        keep_time_billing_item: []
      },
      demandItem: [],
      payType: "",
      demandRules: {
        billing_cycle_unit: [
          {
            required: true,
            message: `${lang.select}${lang.mf_pay_cycle}`,
            type: "error",
          },
        ],
        billing_cycle_day: [
          {
            required: true,
            message: `${lang.select}`,
            type: "error",
          },
        ],
        billing_cycle_point: [
          {
            required: true,
            message: `${lang.select}`,
            type: "error",
          },
        ],
        min_credit: [
          {
            required: true,
            message: `${lang.input}${lang.mf_demand_tip2}`,
            type: "error",
          },
        ],
        min_usage_time: [
          {
            required: true,
            message: `${lang.input}${lang.mf_demand_tip3}`,
            type: "error",
          },
        ],
        min_usage_time_unit: [
          {
            required: true,
            message: `${lang.select}`,
            type: "error",
          },
        ],
        upgrade_min_billing_time: [
          {
            required: true,
            message: `${lang.input}${lang.mf_demand_tip13}`,
            type: "error",
          },
        ],
        upgrade_min_billing_time_unit: [
          {
            required: true,
            message: `${lang.select}`,
            type: "error",
          },
        ],
        grace_time: [
          {
            required: true,
            message: `${lang.input}${lang.mf_grace_time}`,
            type: "error",
          },
        ],
        grace_time_unit: [
          {
            required: true,
            message: `${lang.select}${lang.mf_grace_time}`,
            type: "error",
          },
        ],
        keep_time: [
          {
            required: true,
            message: `${lang.input}${lang.mf_keep_time}`,
            type: "error",
          },
        ],
        keep_time_unit: [
          {
            required: true,
            message: `${lang.select}${lang.mf_keep_time}`,
            type: "error",
          },
        ],
        // keep_time_billing_item: [
        //   {
        //     required: true,
        //     message: `${lang.select}${lang.mf_demand_tip4}`,
        //     type: "error",
        //   },
        // ],
      },
      cycleUnitList: [
        { value: 'hour', label: lang.mf_per_hour },
        { value: 'day', label: lang.mf_per_day },
        { value: 'month', label: lang.mf_per_month },
      ],
      timeArr: [
        { value: 'second', label: lang.mf_second },
        { value: 'minute', label: lang.mf_minute },
        { value: 'hour', label: lang.mf_hour },
      ],
      keepArr: [
        { value: 'hour', label: lang.mf_hour },
        { value: 'day', label: lang.mf_day },
      ],
      radioOption: [
        {
          value: 1,
          label: lang.mf_yes,
        },
        {
          value: 0,
          label: lang.mf_no,
        },
      ],
      dayArr: [],
      /* 三方镜像 */
      imageType: "public",
      /* 安全组 */
      protocol: [
        // 协议选项
        {
          label: "all",
          value: "all",
        },
        {
          label: "all_tcp",
          value: "all_tcp",
        },
        {
          label: "all_udp",
          value: "all_udp",
        },
        {
          label: "tcp",
          value: "tcp",
        },
        {
          label: "udp",
          value: "udp",
        },
        {
          label: "icmp",
          value: "icmp",
        },
        {
          label: "ssh",
          value: "ssh",
        },
        {
          label: "telnet",
          value: "telnet",
        },
        {
          label: "http",
          value: "http",
        },
        {
          label: "https",
          value: "https",
        },
        {
          label: "mssql",
          value: "mssql",
        },
        {
          label: "Oracle",
          value: "oracle",
        },
        {
          label: "mysql",
          value: "mysql",
        },
        {
          label: "rdp",
          value: "rdp",
        },
        {
          label: "postgresql",
          value: "postgresql",
        },
        {
          label: "redis",
          value: "redis",
        },
      ],
      securityGroupList: [],
      securityGroupLoading: false,
      securityGroupModel: false,
      resetSecurityGroupVisible: false,
      isEditingSecurityGroup: false, // 标志位：是否正在编辑（避免回填时触发watch）
      securityGroupForm: {
        product_id: "",
        description: "",
        protocol: "",
        port: "",
      },
      securityGroupColumns: [
        {
          colKey: "drag",
          cell: "drag",
          width: 50,
        },
        {
          colKey: "description",
          title: lang.description,
          ellipsis: true,
        },
        {
          colKey: "protocol",
          title: lang.protocol,
          width: 150,
        },
        {
          colKey: "port",
          title: lang.port,
          width: 150,
        },
        {
          colKey: "op",
          title: lang.operation,
          fixed: "right",
          width: 120,
        },
      ],
      securityGroupRules: {
        description: [
          {
            required: true,
            message: `${lang.input}${lang.description}`,
            type: "error",
          },
        ],
        protocol: [
          {
            required: true,
            message: `${lang.select}${lang.protocol}`,
            type: "error",
          },
        ],
        port: [
          {
            required: true,
            message: `${lang.input}${lang.port}`,
            type: "error",
          },
          {
            validator: (val) => {
              if (!val) return true;
              // 验证单个端口或端口范围
              const portRegex = /^(\d+)(-\d+)?$/;
              if (!portRegex.test(val)) {
                return false;
              }
              const parts = val.split('-');
              const start = parseInt(parts[0]);
              const end = parts[1] ? parseInt(parts[1]) : start;
              return start >= 1 && start <= 65535 && end >= 1 && end <= 65535 && start <= end;
            },
            message: lang.port_range_validation,
            type: "warning",
          },
        ],
      },
    };
  },
  watch: {
    "securityGroupForm.protocol"(val) {
      // 如果是编辑回填，不触发自动填充端口逻辑
      if (this.isEditingSecurityGroup) {
        return;
      }
      switch (val) {
        case "ssh":
          return (this.securityGroupForm.port = "22");
        case "telnet":
          return (this.securityGroupForm.port = "23");
        case "http":
          return (this.securityGroupForm.port = "80");
        case "https":
          return (this.securityGroupForm.port = "443");
        case "mssql":
          return (this.securityGroupForm.port = "1433");
        case "oracle":
          return (this.securityGroupForm.port = "1521");
        case "mysql":
          return (this.securityGroupForm.port = "3306");
        case "rdp":
          return (this.securityGroupForm.port = "3389");
        case "postgresql":
          return (this.securityGroupForm.port = "5432");
        case "redis":
          return (this.securityGroupForm.port = "6379");
        case "tcp":
        case "udp":
          return (this.securityGroupForm.port = "");
        default:
          return (this.securityGroupForm.port = "1-65535");
      }
    },
    "lineForm.line_id" (val) {
      if (val && this.recommendModel) {
        this.getRecommendDefenceName(val);
      }
    },
    "otherForm.type" (val) {
      if (val !== "hyperv") {
        this.natureColumns = this.backNatureColumns;
      } else {
        this.natureColumns = this.backNatureColumns.filter(
          (item) =>
            item.colKey !== "read_bytes" && item.colKey !== "write_bytes"
        );
      }
    },
    "otherForm.backup_enable": {
      handler () {
        if (this.backList.length === 0) {
          this.backList.push({
            num: 1,
            type: "backup",
            price: 0.0,
            status: true,
          });
          this.backAllStatus = true;
        }
      },
      immediate: true,
    },
    "otherForm.snap_enable": {
      handler () {
        if (this.snapList.length === 0) {
          this.snapList.push({
            num: 1,
            type: "snap",
            price: 0.0,
            status: true,
          });
          this.backAllStatus = true;
        }
      },
      immediate: true,
    },
    store_limit: {
      immediate: true,
      handler (val) {
        if (val * 1) {
          this.getStoreLimitList("system_disk_limit");
          this.getStoreLimitList("data_disk_limit");
        }
      },
    },
  },
  computed: {
    calcBakcColums () {
      return type => {
        let temp = [...this[`${type}Columns`]];
        if (this.calcShowDemand) {
          temp[2].className = '';
          temp[2].minWidth = 180;
          temp.push({
            colKey: "on_demand_price",
            title: lang.mf_demand_price,
            className: "back-price",
            minWidth: 320
          });
        }
        if (this.payType === 'on_demand') {
          temp.splice(2, 1);
        }
        return temp;
      };
    },
    calcShowDemand () {
      return (this.payType === 'on_demand' || this.payType === 'recurring_prepayment_on_demand') && !this.isAgent;
    },
    calcModelColumns () {
      const columns = JSON.parse(JSON.stringify(this.recommendColumns));
      if (!this.isAgent) {
        columns.splice(
          -1,
          0,
          {
            colKey: "ontrial",
            title: lang.ontrial_text19,
            ellipsis: true,
            width: 120,
          },

          {
            colKey: "ontrial_price",
            title: lang.ontrial_text20,
            ellipsis: true,
            width: 130,
          },
          {
            colKey: "ontrial_qty",
            title: lang.ontrial_text21,
            ellipsis: true,
            width: 110,
          }
        );
      }
      return columns;
    },
    // 处理级联数据
    calcCascadeImage () {
      const temp = this.systemGroup
        .reduce((all, cur) => {
          all.push({
            id: `f-${cur.id}`,
            name: cur.name,
            children: this.systemList.filter(
              (item) => item.image_group_id === cur.id
            ),
          });
          return all;
        }, [])
        .filter((item) => item.children.length > 0);
      return temp;
    },
    calcLimitRules () {
      return (rule) => {
        return Object.entries(rule);
      };
    },
    calcLimitName () {
      return (type) => {
        return this.limitTypeObj[type]?.name || type;
      };
    },
    disabeldCheck () {
      return (type, name) => {
        // bw 和 flow 互斥逻辑
        if (this.ruleLimit.includes("bw")) {
          if (name === "bw" || name === "flow") {
            if (type === "rule" && name === "bw") {
              return false;
            } else {
              return true;
            }
          }
        }
        if (this.ruleLimit.includes("flow")) {
          if (name === "bw" || name === "flow") {
            if (type === "rule" && name === "flow") {
              return false;
            } else {
              return true;
            }
          }
        }
        if (this.resultLimit.includes("bw")) {
          if (name === "bw" || name === "flow") {
            if (type === "result" && name === "bw") {
              return false;
            } else {
              return true;
            }
          }
        }
        if (this.resultLimit.includes("flow")) {
          if (name === "bw" || name === "flow") {
            if (type === "result" && name === "flow") {
              return false;
            } else {
              return true;
            }
          }
        }
        return this[`${type === "rule" ? "result" : "rule"}Limit`].includes(
          name
        );
      };
    },
    showLimitItem () {
      return (type, name) => {
        return this[`${type}Limit`].includes(name);
      };
    },
    calcCheckbox () {
      return Object.values(this.limitTypeObj).filter(
        (item) => item.type !== "system_disk"
      );
    },
    calcResultCheckbox () {
      const temp = JSON.parse(JSON.stringify(this.limitTypeObj));
      delete temp.data_center;
      return Object.values(temp);
    },
    isShowFill () {
      return (price) => {
        const index = price.findIndex((item) => item.price);
        return index === -1;
      };
    },
    calcCountryName () {
      return (item) => {
        const lang = localStorage.getItem("backLang") || "zh-cn";
        if (lang === "zh-cn") {
          return item.name_zh;
        } else {
          return item.name;
        }
      };
    },
    calcName () {
      return (type) => {
        switch (type) {
          case "memory":
            return `${lang.memory_config}`;
          case "system_disk":
            return `${lang.system_disk_size}${lang.capacity}`;
          case "data_disk":
            return `${lang.data_disk}${lang.capacity}`;
          case "line_bw":
            return `${lang.bw}（Mbps）`;
          case "line_ip":
            return `IPv${this.ipType === "ipv4" ? 4 : 6}${lang.auth_num}（${lang.one
              }）`;
        }
      };
    },
    calcIcon () {
      return (
        this.host +
        "/upload/common/country/" +
        this.countryList.filter(
          (item) => item.id === this.dataForm.country_id
        )[0]?.iso +
        ".png"
      );
    },
    calcIcon1 () {
      if (!this.countrySelect) {
        return;
      }
      return (
        this.host +
        "/upload/common/country/" +
        this.countrySelect.filter(
          (item) => item.id === this.lineForm.country_id
        )[0]?.iso +
        ".png"
      );
    },
    calcCity () {
      if (!this.countrySelect) {
        return;
      }
      const city =
        this.countrySelect.filter(
          (item) => item.id === this.lineForm.country_id
        )[0]?.city || [];
      if (city.length === 1) {
        this.lineForm.city = city[0].name;
      }
      return city;
    },
    calcArea () {
      if (!this.countrySelect) {
        return;
      }
      const area =
        this.countrySelect
          .filter((item) => item.id === this.lineForm.country_id)[0]
          ?.city.filter((item) => item.name === this.lineForm.city)[0]?.area ||
        [];
      if (area.length === 1) {
        this.lineForm.data_center_id = area[0].id;
      }
      return area;
    },
    calcSelectLine () {
      if (!this.countrySelect) {
        return;
      }
      const line =
        this.countrySelect
          .filter((item) => item.id === this.lineForm.country_id)[0]
          ?.city.filter((item) => item.name === this.lineForm.city)[0]
          ?.area.filter((item) => item.id === this.lineForm.data_center_id)[0]
          ?.line || [];
      if (line.length === 1) {
        this.lineForm.line_id = line[0].id;
        this.calcLineType = line[0].bill_type;
      }
      return line;
    },
    calcColums () {
      return (val) => {
        const temp = JSON.parse(JSON.stringify(this.bwColumns));
        switch (val) {
          case "flow":
            temp.splice(1, 0, {
              colKey: "out_bw",
              title: lang.mf_demand_tip17 + '（M）',
            });
            temp[0].title = lang.cloud_flow + "（GB）";
            return temp;
          case "defence":
            temp[0].title = this.lineForm.sync_firewall_rule
              ? lang.mf_tip55
              : lang.defence + "（Gbps）";
            temp.unshift({
              colKey: "drag",
              cell: "drag",
              width: 90,
            });
            return temp;
          case "ipv4":
            temp[0].title = "IPV4" + lang.auth_num + `（${lang.one}）`;
            return temp;
          case "ipv6":
            temp[0].title = "IPV6" + lang.auth_num + `（${lang.one}）`;
            return temp;
          case "line_gpu":
            temp[0].title = "GPU" + lang.auth_num;
            return temp;
          case "global_defence":
            temp[0].title = lang.mf_tip55;
            temp.unshift({
              colKey: "drag",
              cell: "drag",
              width: 90,
            });
            return temp;
        }
      };
    },
    calcSubTitle () {
      // 副标题
      return (data) => {
        if (data.length > 0) {
          return ('- ' + lang[`mf_${data[0].type}`] + lang.mf_way);
        } else {
          return "";
        }
      };
    },
    calcPrice () {
      // 处理本地价格展示
      return (price) => {
        // 找到价格最低的
        const arr = Object.values(price)
          .sort((a, b) => {
            return a - b;
          })
          .filter(Number);
        if (arr.length > 0) {
          let temp = "";
          Object.keys(price).forEach((item) => {
            if (price[item] * 1 === arr[0] * 1) {
              const name = this.cycleData.filter((el) => el.id === item * 1)[0]
                ?.name;
              temp = (arr[0] * 1).toFixed(2) + "/" + name;
            }
          });
          return temp;
        } else {
          return "0.00";
        }
      };
    },
    // 子项的计费方式是否可选
    calcShow () {
      switch (this.subType) {
        case "line_bw":
          return this.lineForm.bw_data.length > 0 ? true : false;
        case "line_ip":
          if (this.ipType === "ipv4") {
            return this.lineForm.ip_data.length > 0 ? true : false;
          } else {
            return this.lineForm.ipv6_data.length > 0 ? true : false;
          }
      }
    },
    calcLimitData () {
      return (name) => {
        return this[`${name}_list`];
      };
    },
    calcLabel () {
      return (type, val) => {
        if (type === "cpu") {
          return val + lang.cores;
        }
        if (type === "memory") {
          return val + this.memory_unit;
        }
      };
    },
    calcLine () {
      // 当前线路
      return this.dataList.filter(
        (item) =>
          item.country_id === this.lineForm.country_id &&
          item.city === this.lineForm.city
      )[0]?.line;
    },
    calcMemery () {
      return (data) => {
        return data.split(",");
      };
    },
    calcRange () {
      // 计算验证范围
      return (val) => {
        if (this.calcType === "memory") {
          // 内存
          if (this.calcForm.memory_unit === "GB") {
            return val >= 1 && val <= 512;
          } else {
            return val >= 128 && val <= 524288;
          }
        } else {
          return val >= 1 && val <= 1048576;
        }
      };
    },
    calcReg () {
      // 动态生成规则
      return (name, min, max) => {
        return [
          { required: true, message: `${lang.input}${name}`, type: "error" },
          {
            pattern: /^[0-9]*$/,
            message: lang.input + `${min}-${max}` + lang.verify1,
            type: "warning",
          },
          {
            validator: (val) => val >= min && val <= max,
            message: lang.input + `${min}-${max}` + lang.verify1,
            type: "warning",
          },
        ];
      };
    },
    calcUnit () {
      if (this.calcType === "memory") {
        return this.calcForm.memory_unit;
      } else {
        return "GB";
      }
    },
    calcPlaceh () {
      if (this.calcType === "memory") {
        return this.calcForm.memory_unit === "GB"
          ? lang.mf_tip9
          : lang.mf_tip33;
      } else {
        return lang.mf_tip9;
      }
    },
    calcMemeryColumns () {
      if (this.memoryList.length === 0) {
        return this.memoryColumns;
      } else {
        const temp = JSON.parse(JSON.stringify(this.memoryColumns));
        temp[0].title = `${lang.memory}（MB）`;
        return this.memory_unit === "MB" ? temp : this.memoryColumns;
      }
    },
    calcRangeSelect () {
      // 处理升降级范围
      return (dataId, id) => {
        const temp = this.recommendList
          .filter((item) => item.data_center_id === dataId && item.id !== id)
          .map((item) => {
            item.id = String(item.id);
            return item;
          });
        const res = [
          {
            name: lang.no_upgrade,
            id: "t0",
          },
          {
            name: lang.all_package,
            id: "t1",
          },
        ];
        if (JSON.parse(JSON.stringify(temp)).length > 0) {
          res.push({
            name: lang.custom_reason,
            id: "t2",
            children: temp,
          });
        }
        return res;
      };
    },
    calcSystem () {
      return this.systemList.filter(
        (item) => item.image_group_id === this.lineForm.image_group_id
      );
    },
    calcLoading () {
      return (name) => {
        return this[`${name}_loading`];
      };
    },
  },
  methods: {
    async changeDefaultDuration (row) {
      try {
        const res = await setDefaultDuration({
          id: row.id,
          product_id: this.id,
        });
        this.$message.success(res.data.msg);
        this.getDurationList();
      } catch (error) {
        this.$message.error(error.data.msg);
        this.getDurationList();
      }
    },
    // 修改磁盘限制
    async changeDiskRangeLimit (val, type) {
      try {
        if (type === "num") {
          if (val < 0) {
            this.otherForm.disk_range_limit = 0;
          }
          if (val === this.tempRangeLimit) {
            return;
          }
        }

        const res = await saveDiskRangeLimit({
          product_id: this.id,
          disk_range_limit_switch: this.otherForm.disk_range_limit_switch,
          disk_range_limit: this.otherForm.disk_range_limit,
        });
        this.$message.success(res.data.msg);
        this.getOtherConfig();
      } catch (error) {
        this.$message.error(error.data.msg);
        this.getOtherConfig();
      }
    },
    //#region 按需
    resetMinNum (val, type) {
      if (val < 0) {
        this.demandForm[type] = 0;
      }
    },
    async getDemandConfig () {
      try {
        const res = await getDemandDetail({
          id: this.id,
        });
        this.demandForm = res.data.data;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    async getDemandItem () {
      try {
        const res = await getDemandItem({
          id: this.id,
        });
        this.demandItem = res.data.data.list;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    async submitDemand ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = {
            ...this.demandForm,
            id: this.id
          };
          this.submitLoading = true;
          const res = await saveDemandDetail(params);
          this.submitLoading = false;
          this.$message.success(res.data.msg);
          this.getDemandConfig();
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {

      }
    },
    //#endregion 按需

    /* 新增防火墙 */
    onDefenceDragSort ({ targetIndex, newData }) {
      this.lineForm.defence_data = newData;
      apiDefenceDragSort({
        id: newData[targetIndex].id,
        prev_id: targetIndex === 0 ? 0 : newData[targetIndex - 1].id,
      }).then((res) => {
        this.$message.success(res.data.msg);
      });
    },
    onGlobalDefenceDragSort ({ targetIndex, newData }) {
      this.checkedFirewallRule = newData;
      apiGlobalDefenceDragSort({
        id: newData[targetIndex].id,
        prev_id: targetIndex === 0 ? 0 : newData[targetIndex - 1].id,
      }).then((res) => {
        this.$message.success(res.data.msg);
      });
    },

    async handleGlobal () {
      try {
        await this.getOtherConfig();
        this.getCheckedRule();
        this.defenceModel = true;
        this.lineRight = false;
        this.checkedFirewallRule = [];
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    changeSyncRule (val) {
      if (val === 0) {
        if (this.checkedFirewallRule.length) {
          this.delVisible = true;
          this.delTit = lang.mf_tip60;
          this.delType = "close_firewall";
        } else {
          this.defenceForm.sync_firewall_rule = val;
          this.lineRight = false;
        }
      } else {
        this.defenceForm.sync_firewall_rule = val;
      }
    },
    async closeFirewall () {
      try {
        this.submitLoading = true;
        const res = await saveDefenceConfig({
          product_id: this.id,
          sync_firewall_rule: 0,
          order_default_defence: 0,
        });
        this.$message.success(res.data.msg);
        this.submitLoading = false;
        this.delVisible = false;
        this.lineRight = false;
        this.getOtherConfig();
        this.getCheckedRule();
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    // 获取已导入的规则
    async getCheckedRule (bol = false) {
      try {
        this.checkRuleloading = true;
        const res = await getGlobalDefence({
          product_id: this.id,
        });
        this.checkedFirewallRule = res.data.data.defence_data;
        this.checkRuleloading = false;
        if (bol) {
          this.defenceForm.order_default_defence =
            this.checkedFirewallRule[0]?.value;
        }
        const idArr = this.checkedFirewallRule.map((item) => item.value);
        if (!idArr.includes(this.defenceForm.order_default_defence)) {
          this.defenceForm.order_default_defence =
            this.checkedFirewallRule[0]?.value;
        }
      } catch (error) {
        this.checkRuleloading = false;
        this.$message.error(error.data.msg);
      }
    },
    rehandleSelectRule (value, { selectedRowData }) {
      this.checkRuleId = value;
      this.checkRuleArr = selectedRowData;
    },
    async importRule () {
      try {
        if (this.checkRuleId.length === 0) {
          return this.$message.error(lang.mf_tip59);
        }
        this.importRuleLoading = true;
        // 导入之前，如果当前开关未开启，先启用开关
        if (this.otherForm.sync_firewall_rule === 0) {
          await saveDefenceConfig({
            product_id: this.id,
            sync_firewall_rule: 1,
            order_default_defence: 0,
          });
        }
        const res = await importDefenceRule({
          product_id: this.id,
          firewall_type: this.defenceForm.firewall_type,
          defence_rule_id: this.checkRuleId,
        });
        this.$message.success(res.data.msg);
        this.importRuleLoading = false;
        this.getCheckedRule(!this.defenceForm.order_default_defence);
        this.checkRuleId = [];
      } catch (error) {
        this.$message.error(error.data.msg);
        this.importRuleLoading = false;
      }
    },
    async editRuleItem (row) {
      try {
        const res = await getGlobalDefenceDetail({
          id: row.id,
        });
        this.defenceRuleName = row.defence_rule_name;
        this.defencePeak = row.defense_peak;
        this.defenceOrder = row.order;
        const { duration } = res.data.data;
        this.defenceForm.id = row.id;
        this.defenceForm.price = duration;
        this.lineRight = true;
        this.isShowLinePrice = true;
        this.optType = "edit";
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    delRuleItem (row) {
      if (this.isAgent) {
        return;
      }
      this.delId = row.id;
      this.delTit = lang.sureDelete;
      this.delType = "rule_item";
      this.delVisible = true;
    },
    async submitRuleSub () {
      try {
        this.submitLoading = true;
        const res = await editGlobalDefence({
          id: this.defenceForm.id,
          order: this.defenceOrder,
          price: this.defenceForm.price.reduce((all, cur) => {
            cur.price && (all[cur.id] = cur.price);
            return all;
          }, {}),
        });
        this.submitLoading = false;
        this.$message.success(res.data.msg);
        this.getCheckedRule();
        this.lineRight = false;
      } catch (error) {
        this.$message.error(error.data.msg);
        this.submitLoading = false;
      }
    },
    async sureDelRuleItem () {
      try {
        this.submitLoading = true;
        const res = await delGlobalDefence({ id: this.delId });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.submitLoading = false;
        this.getCheckedRule();
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    disabledRule (row) {
      const targrt = this.defenceModel
        ? this.checkedFirewallRule
        : this.lineForm.defence_data;
      return (targrt || []).map((item) => item.value).includes(row.value);
    },
    changeFirewallType (type) {
      this.checkRuleId = [];
      this.allFirewallRule =
        this.allFirewallRuleType.filter((item) => item.type === type)[0]
          ?.list || [];
    },
    async getAllRules () {
      try {
        this.isShowLinePrice = false;
        this.checkRuleId = [];
        this.firewallLoading = true;
        const res = await getDefenceRule({
          product_id: this.id,
        });
        this.allFirewallRuleType = res.data.data.rule || [];
        this.defenceForm.firewall_type = this.allFirewallRuleType[0]?.type;
        this.allFirewallRule =
          this.allFirewallRuleType[0]?.list.map((item) => {
            item.value = `${this.defenceForm.firewall_type}_${item.id}`;
            return item;
          }) || [];
        this.lineRight = true;
        this.optType = "add";
        this.subType = "line_defence";
        this.firewallLoading = false;
      } catch (error) {
        this.firewallLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    async submitGlobalRule ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          this.submitLoading = true;
          const res = await saveDefenceConfig({
            product_id: this.id,
            sync_firewall_rule: this.defenceForm.sync_firewall_rule,
            order_default_defence: this.defenceForm.order_default_defence || 0,
          });
          this.$message.success(res.data.msg);
          this.getOtherConfig();
          this.defenceModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.defenceModel = false;
          this.$message.error(error.data.msg);
        }
      } else {

      }
    },
    // 线路相关
    // 开启防护价格配置
    async changeLineDefenceEnable (val) {
      try {
        if (!this.otherForm.sync_firewall_rule) {
          return;
        }
        if (this.lineType === "add") {
          this.lineForm.sync_firewall_rule = this.otherForm.sync_firewall_rule;
        }
        if (val && this.lineForm.sync_firewall_rule) {
          // 新增线路时根据全局配置处理数据
          if (this.lineType === "add") {
            await this.getCheckedRule();
            if (this.checkedFirewallRule.length === 0) return;
            this.lineForm.defence_data = this.checkedFirewallRule.map(
              (item) => {
                return {
                  id: item.id,
                  value: item.value,
                  defense_peak: item.defense_peak,
                  defence_rule_name: item.defence_rule_name,
                  firewall_type: this.checkedFirewallRule[0].firewall_type,
                  defence_rule_id: item.defence_rule_id,
                  price: item.duration_price.reduce((all, cur) => {
                    cur.price && (all[cur.id] = cur.price);
                    return all;
                  }, {}),
                  duration: item.duration_price,
                };
              }
            );
            this.lineForm.order_default_defence =
              this.lineForm.defence_data[0]?.value;
          } else {
            // 编辑的时候先不导入全局防御
            // if (this.lineForm.defence_data.length) {
            //   return;
            // }
            // const res = await lineImportDefenceRule({
            //   line_id: this.lineForm.id,
            //   firewall_type: this.checkedFirewallRule[0]?.firewall_type,
            //   defence_rule_id: this.checkedFirewallRule.map(item => item.defence_rule_id)
            // });
            // this.$message.success(res.data.msg);
            // this.lineForm.order_default_defence = this.lineForm.defence_data[0]?.value;
            // this.submitLine({ validateResult: true, firstError: "" }, false);
          }
        }
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    async changeLineFirewallRule (val) {
      this.lineRight = false;
      // 清空掉之前的数据： 开启清空的是之前手动添加的，关闭清空防火墙类型的
      if (val) {
        if (this.lineType === "add") {
          this.lineForm.sync_firewall_rule = val;
          this.lineForm.defence_data = this.checkedFirewallRule.map((item) => {
            return {
              id: item.id,
              value: item.value,
              defense_peak: item.defense_peak,
              defence_rule_name: item.defence_rule_name,
              firewall_type: this.checkedFirewallRule[0].firewall_type,
              defence_rule_id: item.defence_rule_id,
              price: item.duration_price.reduce((all, cur) => {
                cur.price && (all[cur.id] = cur.price);
                return all;
              }, {}),
              duration: item.duration_price,
            };
          });
        } else {
          // 编辑的时候暂不导入全局的防御
          // await this.getCheckedRule();
          // // 先开启开关
          // await this.submitLine({ validateResult: true, firstError: "" }, false);
          // const res = await lineImportDefenceRule({
          //   line_id: this.lineForm.id,
          //   firewall_type: this.checkedFirewallRule[0]?.firewall_type,
          //   defence_rule_id: this.checkedFirewallRule.map(item => item.defence_rule_id)
          // });
          // this.$message.success(res.data.msg);
          // this.lineForm.order_default_defence = this.checkedFirewallRule[0]?.value;
          if (this.lineForm.defence_data.length > 0) {
            this.tempSyncSwitch = val;
            this.delVisible = true;
            this.delTit = val ? lang.mf_tip62 : lang.mf_tip60;
            this.delType = "close_line_firewall";
          } else {
            this.lineForm.sync_firewall_rule = val;
            this.submitLine({ validateResult: true, firstError: "" }, false);
          }
        }
      } else {
        if (this.lineType === "add") {
          this.lineForm.sync_firewall_rule = val;
          this.lineForm.defence_data = [];
        } else {
          if (this.lineForm.defence_data.length > 0) {
            this.tempSyncSwitch = val;
            this.delVisible = true;
            this.delTit = val ? lang.mf_tip62 : lang.mf_tip60;
            this.delType = "close_line_firewall";
          } else {
            this.lineForm.sync_firewall_rule = val;
            this.submitLine({ validateResult: true, firstError: "" }, false);
          }
        }
      }
    },
    chooseDelPro (val) {
      this.payOntrialForm.old_client_exclusive = val;
    },

    closeLineFirewall () {
      this.delVisible = false;
      this.lineForm.sync_firewall_rule = this.tempSyncSwitch;
      this.lineForm.order_default_defence = 0;
      this.submitLine({ validateResult: true, firstError: "" }, false);
    },
    async importLineRule () {
      try {
        if (this.checkRuleId.length === 0) {
          return this.$message.error(lang.mf_tip59);
        }
        this.importRuleLoading = true;
        if (this.lineType === "add") {
          const temp = this.checkRuleArr.map((item) => {
            return {
              defence_rule_id: item.id,
              value: item.value,
              defense_peak: item.defense_peak,
              firewall_type: this.defenceForm.firewall_type,
              price: this.cycleData.reduce((all, cur) => {
                cur.price && (all[cur.id] = cur.price);
                return all;
              }, {}),
              duration: this.handlerAddPrice(),
            };
          });
          this.lineForm.defence_data.push(...temp);
          this.checkRuleId = [];
          this.checkRuleArr = [];
        } else {
          const res = await lineImportDefenceRule({
            line_id: this.lineForm.id,
            firewall_type: this.defenceForm.firewall_type,
            defence_rule_id: this.checkRuleId,
          });
          const result = await getLineDetails({ id: this.lineForm.id });
          if (!this.lineForm.order_default_defence) {
            this.lineForm.order_default_defence =
              result.data.data.defence_data[0]?.value;
          }
          this.$message.success(res.data.msg);
          this.submitLine({ validateResult: true, firstError: "" }, false);
        }
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    async getRecommendDefenceName (id) {
      try {
        this.recommendDefenceName = "";
        const res = await getLineDetails({ id });
        const { sync_firewall_rule, order_default_defence, defence_data } =
          res.data.data;
        if (sync_firewall_rule) {
          this.recommendDefenceName =
            defence_data.find((item) => item.id === order_default_defence)
              ?.defense_peak || "--";
        }
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    /* 新增防火墙 end */

    handelPullOs (type) {
      if (
        (type === "local" && !this.isLocalSystem) ||
        (type === "net" && this.isLocalSystem)
      ) {
        this.pullOsVisible = true;
      } else {
        const subApi = type === "local" ? apiPullLocalImage : refreshImage;
        this.pullSystemList(subApi);
      }
    },
    surePullOs () {
      const subApi = this.isLocalSystem ? refreshImage : apiPullLocalImage;
      this.pullSystemList(subApi);
    },
    async pullSystemList (subApi) {
      this.pullOsLoading = true;
      try {
        this.$message.success(lang.mf_tip);
        await subApi({
          product_id: this.id,
        });
        this.pullOsLoading = false;
        this.getSystemList();
        this.getGroup();
        this.getOtherConfig();
        this.pullOsVisible = false;
      } catch (error) {
        this.pullOsLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    async getPlugin () {
      try {
        const res = await getActivePlugin();
        const temp = res.data.data.list.reduce((all, cur) => {
          all.push(cur.name);
          return all;
        }, []);
        this.hasFirewall =
          temp.includes("AodunFirewall") || temp.includes("AodunFirewallAgent");
        const hasMultiLanguage = temp.includes("MultiLanguage");
        this.multiliTip = hasMultiLanguage
          ? `(${lang.support_multili_mark})`
          : "";
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    closeUpgradeRange () {
      this.upgradeModel = false;
    },
    async getServerInfo () {
      try {
        const res = await getServeProductDetail(this.id);
        this.isAgent = res.data.data.product.mode === "sync";
        this.isFirewallProduct =
          res.data.data.product.plugin_custom_fields?.aodun_firewall_product ||
          0;
        if (
          res.data.data.product.pay_ontrial &&
          Object.keys(res.data.data.product.pay_ontrial).length > 0
        ) {
          this.payOntrialForm = res.data.data.product.pay_ontrial;
        }
        this.payType = res.data.data.product.pay_type;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 切换线路显示
    async changeShowLine (val, row) {
      try {
        const res = await changeLineStatus({
          id: row.id,
          hidden: val,
        });
        this.$message.success(res.data.msg);
        this.getDataList();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    getQuery (name) {
      const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
      const r = window.location.search.substr(1).match(reg);
      if (r != null) return decodeURI(r[2]);
      return null;
    },
    chooseLimit (e) {
      if (e.includes("recommend_config")) {
        this.ruleLimit = ["recommend_config", "duration"];
      }
    },
    changeLimitRange (val, name, type, model, index, max) {
      max = max || 9999999;
      // rule 部分的 bw/flow/ipv4_num 是对象，result 部分是数组
      let target;
      if (model === "rule") {
        target = this.limitForm[model][name];
      } else {
        target = this.limitForm[model][name][index];
      }

      if (!target) return;

      let min = name === "system_disk" ? 1 : 0;
      if (val < min) {
        target[type] = min;
      }
      if (val > max) {
        target[type] = max;
      }
      if (
        typeof target.max !== "object" &&
        target.max !== ""
      ) {
        if (type === "min" && val >= target.max) {
          target.max = target.min;
        }
        if (type === "max" && val <= target.min) {
          target.min = target.max;
        }
      }
    },
    changeNum (val, min, max, name) {
      if (val < min) {
        this.otherForm[name] = min;
      }
      if (val > max) {
        this.otherForm[name] = max;
      }
      if (
        this.otherForm.rand_ssh_port_start >= this.otherForm.rand_ssh_port_end
      ) {
        this.otherForm.rand_ssh_port_end =
          this.otherForm.rand_ssh_port_start + 1;
      }
    },
    changePort (val, name) {
      if (val < 1) {
        this.otherForm[name] = 1;
      }
      if (val > 65535) {
        this.otherForm[name] = 65535;
      }
    },
    async onChange (row) {
      try {
        const res = await changePackageShow({
          id: row.id,
          hidden: row.hidden,
        });
        this.$message.success(res.data.msg);
        this.getRecommendList();
      } catch (error) {
        this.$message.error(error.data.msg);
        this.getRecommendList();
      }
    },

    async ontrialChange (row) {
      try {
        const res = await changeOntrialShow({
          id: row.id,
          ontrial: row.ontrial,
        });
        this.$message.success(res.data.msg);
        this.getRecommendList();
      } catch (error) {
        this.$message.error(error.data.msg);
        this.getRecommendList();
      }
    },

    async onChangeUpgrade (row) {
      try {
        const res = await changePackageUpgradeShow({
          id: row.id,
          upgrade_show: row.upgrade_show,
        });
        this.$message.success(res.data.msg);
        this.getRecommendList();
      } catch (error) {
        this.$message.error(error.data.msg);
        this.getRecommendList();
      }
    },
    async autoFill (name, data) {
      try {
        const price = JSON.parse(JSON.stringify(data)).reduce((all, cur) => {
          if (cur.price) {
            all[cur.id] = cur.price;
          }
          return all;
        }, {});
        const params = {
          product_id: this.id,
          price,
        };
        const res = await fillDurationRatio(params);
        const fillPrice = res.data.data.list;
        this[name].price = this[name].price.map((item) => {
          item.price = fillPrice[item.id];
          return item;
        });
        this[name].on_demand_price = res.data.data.on_demand_price;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    async changeSort (e) {
      try {
        this.systemGroup = e.newData;
        const image_group_order = e.newData.reduce((all, cur) => {
          all.push(cur.id);
          return all;
        }, []);
        const res = await changeImageGroup({ image_group_order });
        this.$message.success(res.data.msg);
        this.getGroup();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 切换选项卡
    changeTab (e) {
      this.allStatus = false;
      this.backAllStatus = false;
      this.lineType = "";
      switch (e) {
        case "duration":
          this.getDurationList();
          break;
        case "demand":
          this.getDemandConfig();
          this.getDemandItem();
          for (i = 1; i <= 31; i++) {
            this.dayArr.push({
              value: i,
              label: `${i}${lang.mf_demand_tip14}`
            });
          }
          break;
        case "calc":
          this.getCpuList();
          this.getMemoryList();
          this.getDurationList();
          break;
        case "data_center":
          this.getDataList();
          this.getCountryList();
          this.chooseData();
          this.getDurationList();
          break;
        case "store":
          this.getOtherConfig();
          this.getStoreList("system_disk");
          this.getStoreList("data_disk");
          this.getDiskTypeList("data_disk");
          this.getDurationList();
          break;
        case "limit":
          this.getConfigLimitList();
          this.getCpuList();
          this.getMemoryList();
          this.getDataList();
          this.getGroup();
          this.getSystemList();
          this.getRecommendList();
          this.getDurationList();
          break;
        case "recommend": // 套餐
          this.getRecommendList();
          this.getMemoryList();
          this.chooseData();
          // this.getDiskTypeList("system_disk");
          // this.getDiskTypeList("data_disk");
          this.calcType = "memory";
          break;
        case "system":
          this.getSystemList();
          this.getGroup();
          break;
        case "ontrial":
          this.getServerInfo();
          break;
        case "other":
          this.getOtherConfig();
          this.getDurationList();
          break;
        case "security_group":
          this.getSecurityGroupList();
          break;
        default:
          break;
      }
    },
    checkLimit (val) {
      const reg = /^[0-9]*$/;
      if (reg.test(val) && val >= 0 && val <= 9999999) {
        return { result: true };
      } else {
        return {
          result: false,
          message: lang.input + "0~9999999" + lang.verify18,
          type: "warning",
        };
      }
    },
    changeMinMemory (val) {
      if (this.lineForm.max_memory) {
        if (val * 1 >= this.lineForm.max_memory * 1) {
          this.lineForm.min_memory = val >= 524288 ? val - 1 : val;
          this.lineForm.max_memory = this.lineForm.min_memory * 1 + 1;
        }
      }
    },
    changeMaxMemory (val) {
      if (this.lineForm.min_memory) {
        if (val * 1 <= this.lineForm.min_memory * 1) {
          this.lineForm.max_memory = this.lineForm.max_memory >= 2 ? val : 2;
          this.lineForm.min_memory = this.lineForm.max_memory * 1 - 1;
        }
      }
    },
    blurDemandPrice (val, form, price = 'on_demand_price') {
      let temp = String(val).match(/^\d*(\.?\d{0,4})/g)[0] || "";
      if (temp && !isNaN(Number(temp))) {
        temp = Number(temp).toFixed(4);
      }
      if (temp >= 9999999) {
        this[form][price] = Number(9999999).toFixed(2);
      } else {
        this[form][price] = temp;
      }
    },
    // 处理价格
    blurPrice (val, ind, form) {
      let temp = String(val).match(/^\d*(\.?\d{0,2})/g)[0] || "";
      if (temp && !isNaN(Number(temp))) {
        temp = Number(temp).toFixed(2);
      }
      if (temp >= 9999999) {
        this[form].price[ind].price = Number(9999999).toFixed(2);
      } else {
        this[form].price[ind].price = temp;
      }
    },
    blurGpuPrice (val, ind) {
      let temp = String(val).match(/^\d*(\.?\d{0,2})/g)[0] || "";
      if (temp && !isNaN(Number(temp))) {
        temp = Number(temp).toFixed(2);
      }
      if (temp >= 9999999) {
        val = 9999999.0;
        this.gpuForm.price[ind].price = Number(9999999).toFixed(2);
      } else {
        this.gpuForm.price[ind].price = temp;
      }
    },
    blurSubPrice (val, ind) {
      let temp = String(val).match(/^\d*(\.?\d{0,2})/g)[0] || "";
      if (temp && !isNaN(Number(temp))) {
        temp = Number(temp).toFixed(2);
      }
      if (temp >= 9999999) {
        val = 9999999.0;
        this.subForm.price[ind].price = Number(9999999).toFixed(2);
      } else {
        this.subForm.price[ind].price = temp;
      }
    },
    blurPackagePrice (val, ind) {
      let temp = String(val).match(/^\d*(\.?\d{0,2})/g)[0] || "";
      if (temp && !isNaN(Number(temp))) {
        temp = Number(temp).toFixed(2);
      }
      if (temp >= 9999999) {
        this.lineForm.price[ind].price = Number(9999999).toFixed(2);
      } else {
        this.lineForm.price[ind].price = temp;
      }
    },
    /* 配置限制 */
    subResult (name, index) {
      this.limitForm.result[name].splice(index, 1);
    },
    addResult (name) {
      const rangeTypes = ["memory", "system_disk", "bw", "flow", "ipv4_num"];
      if (rangeTypes.includes(name)) {
        this.limitForm.result[name].push({
          opt: this.limitForm.result[name][0]?.opt || "eq",
          min: null,
          max: null,
          value: [],
        });
      } else {
        this.limitForm.result[name].push({
          opt: this.limitForm.result[name][0]?.opt || "eq",
          value: [],
          id: [],
        });
      }
    },
    async getConfigLimitList () {
      try {
        this.limitLoading = true;
        const res = await getConfigLimit({
          product_id: this.id,
        });
        this.limitData = res.data.data.list;
        this.limitLoading = false;
      } catch (error) {
        this.limitLoading = false;
      }
    },
    addLimit () {
      this.optType = "add";
      this.limitModel = true;
      this.ruleLimit = [];
      this.resultLimit = [];
      this.limitForm = JSON.parse(JSON.stringify(this.originLimitForm));
      this.comTitle = `${lang.order_text53}${lang.mf_rule}`;
    },
    handleData (temp) {
      const typeArr = [
        "memory",
        "system_disk",
        "data_disk",
        "bw",
        "flow",
        "ipv4_num",
        "ipv6_num",
      ];
      typeArr.forEach((item) => {
        if (temp[item]) {
          if (temp[item].min == "") {
            temp[item].min = null;
          } else {
            temp[item].min = temp[item].min * 1;
          }
          if (temp[item].max == "") {
            temp[item].max = null;
          } else {
            temp[item].max = temp[item].max * 1;
          }
        }
      });
      return temp;
    },
    editLimit (row, type) {
      if (this.isAgent) {
        return;
      }
      this.comTitle =
        (type === "copy" ? lang.mf_copy : lang.edit) + lang.mf_rule;
      this.limitModel = true;
      this.optType = type;
      this.ruleLimit = Object.keys(row.rule);
      this.resultLimit = Object.keys(row.result);
      const tempRule = JSON.parse(JSON.stringify(row)).rule;
      const tempResult = JSON.parse(JSON.stringify(row)).result;
      let temp = JSON.parse(JSON.stringify(this.originLimitForm));
      temp.id = row.id;
      temp.rule = Object.assign(temp.rule, this.handleData(tempRule));
      temp.result = Object.assign(temp.result, this.handleData(tempResult));
      this.limitForm = temp;
    },
    async submitLimit ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const temp = JSON.parse(JSON.stringify(this.limitForm));
          const rule = this.ruleLimit.reduce((all, cur) => {
            if (temp.rule[cur]) {
              all[cur] = temp.rule[cur];
            }
            return all;
          }, {});
          const result = this.resultLimit.reduce((all, cur) => {
            if (temp.result[cur]) {
              all[cur] = temp.result[cur];
            }
            return all;
          }, {});
          let params = {
            id: this.limitForm.id,
            rule,
            result,
          };
          params.product_id = this.id;
          if (this.optType === "add" || this.optType === "copy") {
            delete params.id;
          }
          this.submitLoading = true;
          const res = await createAndUpdateConfigLimit(this.optType, params);
          this.$message.success(res.data.msg);
          this.getConfigLimitList();
          this.limitModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);

      }
    },
    async delLimit () {
      try {
        this.submitLoading = true;
        const res = await delConfigLimit({ id: this.delId });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getConfigLimitList(this.delType);
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    /* 推荐配置 */
    async getRecommendList () {
      try {
        this.dataLoading = true;
        const res = await getRecommend({
          product_id: this.id,
          page: 1,
          limit: 1000,
        });
        this.recommendList = res.data.data.list.map((item) => {
          item.temp = [];
          // item.id = String(item.id);
          if (item.upgrade_range === 0) {
            item.temp = ["t0"];
          } else if (item.upgrade_range === 1) {
            item.temp = ["t1"];
          } else {
            item.temp = item.rel_id.map((item) => String(item));
          }
          return item;
        });
        this.dataLoading = false;
      } catch (error) {
        this.dataLoading = false;
      }
    },
    async getDiskTypeList (type) {
      try {
        const res = await getDiskType(type, {
          product_id: this.id,
        });
        if (type === "system_disk") {
          this.systemDiskType = res.data.data.list;
        } else {
          this.dataDiskType = res.data.data.list;
        }
      } catch (error) {
        this.dataLoading = false;
      }
    },

    /* 升降级范围 */
    handlerRange () {
      this.upgradeModel = true;
      this.getRecommendList();
    },
    changeRange (e, node, index) {
      if (e.length > 0 && String(node.node.value).indexOf("t") !== -1) {
        if (node.node.value === "t0") {
          this.recommendList[index].temp = ["t0"];
        } else if (node.node.value === "t1") {
          this.recommendList[index].temp = ["t1"];
        } else {
          this.recommendList[index].temp = e.filter(
            (item) => item !== "t0" && item !== "t1"
          );
        }
      } else {
        this.recommendList[index].temp = e.filter(
          (item) => item !== "t0" && item !== "t1"
        );
      }
    },
    async saveUpgrade () {
      try {
        const bol = this.recommendList.every((item) => {
          const len = JSON.parse(JSON.stringify(item.temp)).length;
          return len > 0;
        });
        if (!bol) {
          return this.$message.error(`${lang.select}${lang.demote_range}`);
        }
        const recommend_config = this.recommendList.reduce((all, cur) => {
          if (cur.temp[0] === "t0") {
            all[cur.id] = {
              upgrade_range: 0,
            };
          } else if (cur.temp[0] === "t1") {
            all[cur.id] = {
              upgrade_range: 1,
            };
          } else {
            all[cur.id] = {
              upgrade_range: 2,
              rel_id: cur.temp,
            };
          }
          return all;
        }, {});
        const params = {
          product_id: this.id,
          recommend_config,
        };
        this.submitLoading = true;
        const res = await saveUpgradeRange(params);
        this.$message.success(res.data.msg);
        this.submitLoading = false;
        this.upgradeModel = false;
        this.getRecommendList();
      } catch (error) {
        console.log("@@@@", error);
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    /* 升降级范围 end */
    addRecommend () {
      this.lineForm = {
        country_id: "",
        city: "",
        name: "",
        description: "",
        order: "",
        data_center_id: "",
        cpu: "",
        gpu_num: "",
        memory: "",
        system_disk_size: "",
        system_disk_type: "",
        data_disk_size: "",
        data_disk_type: "",
        ip_num: "",
        ipv6_num: "",
        bw: "",
        in_bw: "",
        traffic_type: 3,
        peak_defence: "",
        flow: "",
        line_id: "",
        ontrial: 0,
        ontrial_price: null,
        ontrial_stock_control: 0,
        ontrial_qty: null,
        due_not_free_gpu: false,
        price: this.handlerAddPrice(),
        on_demand_price: null
      };
      this.optType = "add";
      this.recommendModel = true;
      this.comTitle = `${lang.order_text53}${lang.package}`;
    },
    // 编辑套餐
    async editRecommend (row) {
      try {
        const res = await getRecommendDetails({
          id: row.id,
        });
        this.comTitle = `${lang.edit}${lang.package}`;
        const temp = res.data.data;
        temp.price = temp.duration;
        delete temp.duration;
        temp.due_not_free_gpu = temp.due_not_free_gpu ? true : false;
        this.lineForm = temp;
        this.optType = "update";
        this.recommendModel = true;
        const type = this.countrySelect
          .filter((item) => item.id === this.lineForm.country_id)[0]
          ?.city.filter((item) => item.name === this.lineForm.city)[0]
          ?.area.filter((item) => item.id === this.lineForm.data_center_id)[0]
          ?.line.filter(
            (item) => item.id === this.lineForm.line_id
          )[0]?.bill_type;
        this.calcLineType = type;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    async submitRecommend ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.lineForm));
          if (this.optType === "add") {
            delete params.id;
          }
          if (this.calcLineType === "bw") {
            delete params.flow;
          }
          params.price = params.price.reduce((all, cur) => {
            cur.price && (all[cur.id] = cur.price);
            return all;
          }, {});
          params.due_not_free_gpu = params.due_not_free_gpu ? 1 : 0;
          this.submitLoading = true;
          const res = await createAndUpdateRecommend(this.optType, params);
          this.$message.success(res.data.msg);
          this.getRecommendList(this.calcType);
          this.recommendModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
      }
    },
    async changeOnlyPackage (e) {
      try {
        const res = await changeSalePackage({
          product_id: this.id,
          status: e,
        });
        this.$message.success(res.data.msg);
        this.getOtherConfig();
      } catch (error) {
        this.$message.error(error.data.msg);
        this.getOtherConfig();
      }
    },
    async changeUpgradeTip (e) {
      try {
        const res = await changeUpgradeShow({
          product_id: this.id,
          status: e,
        });
        this.$message.success(res.data.msg);
        this.getOtherConfig();
      } catch (error) {
        this.$message.error(error.data.msg);
        this.getOtherConfig();
      }
    },
    // 删除推荐
    async delRecommend () {
      try {
        this.submitLoading = true;
        const res = await delRecommend({ id: this.delId });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getRecommendList();
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },

    /* 推荐配置 end*/
    /* 线路 */
    addLine () {
      if (this.isAgent) {
        return;
      }
      this.isShowLinePrice = true;
      this.isEditDefence = false;
      this.lineModel = true;
      this.lineType = "add";
      this.dataForm.country_id = "";
      this.lineForm = Object.assign(JSON.parse(JSON.stringify(this.lineForm)), {
        id: "",
        country_id: "", // 线路国家
        city: "", // 线路城市
        data_center_id: "",
        name: "",
        order: 0,
        bill_type: "bw", // bw, flow
        bw_ip_group: "",
        ipv6_group_id: "",
        defence_ip_group: "",
        ip_enable: 0, // ip开关
        ipv6_enable: 0, // ipv6开关
        defence_enable: 0, // 防护开关
        bw_data: [], // 带宽
        flow_data: [], //流量
        defence_data: [], // 防护
        ip_data: [], // ip
        ipv6_data: [],
        link_clone: false,
        // gpu
        gpu_enable: 0,
        gpu_name: "",
        gpu_data: [],
        // 防火墙
        sync_firewall_rule: this.otherForm.sync_firewall_rule,
        order_default_defence: "",
      });
      this.lineRight = false;
    },
    onDragSort ({ targetIndex, newData }) {
      this.systemList = newData;
      apiDragImage({
        id: newData[targetIndex].id,
        prev_id: targetIndex === 0 ? 0 : newData[targetIndex - 1].id,
      }).then((res) => {
        this.$message.success(res.data.msg);
      });
    },
    async editLine (row) {
      try {
        const res = await getLineDetails({ id: row.id });
        this.lineForm = JSON.parse(JSON.stringify(res.data.data));
        this.lineForm.link_clone = this.lineForm.link_clone * 1 ? true : false;
        this.lineType = "update";
        this.optType = "update";
        this.lineRight = false;
        this.lineModel = true;
        this.isShowLinePrice = true;
        this.bw_ip_show = (this.lineForm.bw_ip_group || this.lineForm.ipv6_group_id) ? true : false;
        this.defence_ip_show = this.lineForm.defence_ip_group ? true : false;
        this.subId = row.id;
        this.lineForm.order_default_defence =
          this.lineForm.order_default_defence || "";
      } catch (error) { }
    },
    changeCountry () {
      this.lineForm.city = "";
      this.lineForm.data_center_id = "";
    },
    changeCity () {
      this.lineForm.data_center_id = "";
    },

    // 编辑线路子项
    async editSubItem (row, index, type, ipType) {
      this.ipType = ipType;
      this.subType = type;
      this.optType = "update";
      this.delSubIndex = index;
      this.lineRight = true;
      this.isShowLinePrice = true;
      let temp = "";
      if (this.lineType === "add") {
        temp = row;
      } else {
        if (ipType === "ipv6") {
          type = "line_ipv6";
        }
        const res = await getLineChildDetails(type, { id: row.id });
        temp = res.data.data;
        this.delId = row.id;
      }
      // 是防火墙防御
      if (type === "line_defence" && ipType) {
        this.isEditDefence = true;
        temp.defense_peak = row.defense_peak;
        temp.order = row.order;
        temp.defence_rule_name = row.defence_rule_name;
      } else {
        this.isEditDefence = false;
      }
      setTimeout(() => {
        if (type !== 'line_flow_on_demand') {
          const price = temp.duration
            .reduce((all, cur) => {
              all.push({
                id: cur.id,
                name: cur.name,
                price: cur.price,
              });
              return all;
            }, [])
            .sort((a, b) => {
              return a.id - b.id;
            });
          Object.assign(this.subForm, temp);
          this.subForm.price = price;
        } else {
          Object.assign(this.subForm, temp);
        }
        if (
          this.subForm.other_config.in_bw ||
          this.subForm.other_config.advanced_bw
        ) {
          this.isAdvance = true;
        } else {
          this.isAdvance = false;
        }
      }, 0);
    },
    // 删除线路子项
    async delSubItem () {
      try {
        this.lineRight = false;
        if (this.lineType === "add") {
          // 本地删除
          switch (this.delType) {
            case "line_bw":
              return this.lineForm.bw_data.splice(this.delSubIndex, 1);
            case "line_flow":
              return this.lineForm.flow_data.splice(this.delSubIndex, 1);
            case "line_flow_on_demand":
              return this.lineForm.flow_data_on_demand.splice(this.delSubIndex, 1);
            case "line_defence":
              return this.lineForm.defence_data.splice(this.delSubIndex, 1);
            case "line_ip":
              if (this.ipType === "ipv4") {
                return this.lineForm.ip_data.splice(this.delSubIndex, 1);
              }
              if (this.ipType === "ipv6") {
                return this.lineForm.ipv6_data.splice(this.delSubIndex, 1);
              }
            case "line_gpu":
              return this.lineForm.gpu_data.splice(this.delSubIndex, 1);
          }
        } else {
          // 编辑的时候删除
          this.submitLoading = true;
          let tempType = this.delType;
          if (this.ipType === "ipv4") {
            tempType = "line_ip";
          }
          if (this.ipType === "ipv6") {
            tempType = "line_ipv6";
          }
          const res = await delLineChild(tempType, { id: this.delId });
          this.$message.success(res.data.msg);
          this.delVisible = false;
          // this.editLine({ id: this.subId })
          // 删除防火墙规则时，如果删除当前默认配置的选项，需重置
          if (
            this.delType === "line_defence" &&
            this.lineForm.order_default_defence
          ) {
            const curValue = this.lineForm.defence_data.find(
              (item) => item.id === this.delId
            )?.value;
            if (curValue === this.lineForm.order_default_defence) {
              this.lineForm.order_default_defence =
                this.lineForm.defence_data.filter(
                  (item) => item.value !== curValue
                )[0]?.value || "";
            }
          }
          this.submitLoading = false;
          this.submitLine({ validateResult: true, firstError: "" }, false);
        }
      } catch (error) {
        this.submitLoading = false;
        this.delVisible = false;
        this.$message.error(error.data.msg);
      }
    },
    handlerAddPrice () {
      // 处理新增周期
      const price = this.cycleData
        .reduce((all, cur) => {
          all.push({
            id: cur.id,
            name: cur.name,
            price: "",
          });
          return all;
        }, [])
        .sort((a, b) => {
          return a.id - b.id;
        });
      return price;
    },

    // 新增线路子项
    addLineSub (type, ipType) {
      if (this.isAgent) {
        return;
      }
      this.ipType = ipType;
      this.subType = type;
      this.optType = "add";
      this.isAdvance = false;
      this.subForm.value = "";
      this.subForm.min_value = "";
      this.subForm.max_value = "";
      this.isShowLinePrice = true;
      this.isEditDefence = false;
      // 编辑线路且新增gpu的时候，要先填gpu名称
      if (this.lineType === "update" && type === "line_gpu") {
        if (!this.lineForm.gpu_name) {
          return this.$message.warning(
            `${lang.input}GPU${lang.box_title46}${lang.nickname}`
          );
        }
      }
      if (type === "line_bw") {
        this.subForm.type = this.lineForm.bw_data[0]?.type || "radio";
      }
      if (type === "line_ip") {
        if (ipType === "ipv4") {
          this.subForm.type = this.lineForm.ip_data[0]?.type || "radio";
        } else {
          this.subForm.type = this.lineForm.ipv6_data[0]?.type || "radio";
        }
      }
      this.subForm.value = "";
      this.subForm.other_config = {
        in_bw: "",
        advanced_bw: "",
        traffic_type: 1,
        bill_cycle: "last_30days",
      };
      this.lineRight = true;
      this.isShowLinePrice = true;
      this.subForm.price = this.handlerAddPrice();
      this.bw_ip_show = false;
      this.defence_ip_show = false;
    },
    /* 推荐配置 */
    changeBillType (e) {
      this.calcLineType = this.calcSelectLine.filter(
        (item) => item.id === e
      )[0]?.bill_type;
    },

    // 保存线路子项
    async submitSub ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.subForm));
          params.product_id = this.id;
          params.step = 1;
          this.submitLoading = true;
          const duration = JSON.parse(JSON.stringify(params.price));
          params.price = params.price.reduce((all, cur) => {
            cur.price && (all[cur.id] = cur.price);
            return all;
          }, {});

          // 新增的时候本地处理
          if (this.lineType === "add") {
            params.duration = duration;
            switch (this.subType) {
              case "line_bw":
                this.optType === "add"
                  ? this.lineForm.bw_data.unshift(params)
                  : this.lineForm.bw_data.splice(this.delSubIndex, 1, params);
                break;
              case "line_flow":
                this.optType === "add"
                  ? this.lineForm.flow_data.unshift(params)
                  : this.lineForm.flow_data.splice(this.delSubIndex, 1, params);
                break;
              case "line_flow_on_demand":
                this.optType === "add"
                  ? this.lineForm.flow_data_on_demand.unshift(params)
                  : this.lineForm.flow_data_on_demand.splice(this.delSubIndex, 1, params);
                break;
              case "line_defence":
                this.optType === "add"
                  ? this.lineForm.defence_data.unshift(params)
                  : this.lineForm.defence_data.splice(
                    this.delSubIndex,
                    1,
                    params
                  );
                break;
              case "line_ip":
                let curIpData = "";
                if (this.ipType === "ipv4") {
                  curIpData = "ip_data";
                }
                if (this.ipType === "ipv6") {
                  curIpData = "ipv6_data";
                }
                this.optType === "add"
                  ? this.lineForm[curIpData].unshift(params)
                  : this.lineForm[curIpData].splice(
                    this.delSubIndex,
                    1,
                    params
                  );
                break;
              case "line_gpu":
                this.optType === "add"
                  ? this.lineForm.gpu_data.unshift(params)
                  : this.lineForm.gpu_data.splice(this.delSubIndex, 1, params);
                break;
            }
            this.submitLoading = false;
            this.lineRight = false;
            return;
          }
          // 新增：传线路id，编辑传配置id
          let name = this.subType;
          if (this.ipType === "ipv6") {
            name = "line_ipv6";
          }
          params.id = this.optType === "add" ? this.subId : this.delId;
          const res = await createAndUpdateLineChild(
            name,
            this.optType,
            params
          );
          this.$message.success(res.data.msg);
          // this.editLine({ id: this.subId })
          // 保存子项的时候需要保存线路配置，第一次未开启防护/附加IP的时候，开关会被重置
          this.submitLine({ validateResult: true, firstError: "" }, false);
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);

      }
    },

    async submitLine ({ validateResult, firstError }, bol = true) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.lineForm));
          params.product_id = this.id;
          params.link_clone = params.link_clone ? 1 : 0;
          const isAdd = params.id ? "update" : "add";
          // 新增线路的时候，默认防御传value
          if (isAdd === "add" && params.order_default_defence) {
            params.order_default_defence = params.defence_data.find(
              (item) => item.value === params.order_default_defence
            )?.value;
          }
          this.submitLoading = true;
          if (isAdd === "update") {
            delete params.defence_data;
          }
          const res = await createAndUpdateLine(isAdd, params);
          if (bol) {
            this.$message.success(res.data.msg);
            this.getDataList();
            this.lineModel = false;
            this.lineType = "";
          } else {
            this.editLine({ id: this.subId });
          }
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);

      }
    },

    /* 数据中心 */
    async getDataList () {
      try {
        this.dataLoading = true;
        const res = await getDataCenter({
          product_id: this.id,
          page: 1,
          limit: 1000,
        });
        this.dataList = res.data.data.list;
        this.dataLoading = false;
      } catch (error) {
        this.dataLoading = false;
      }
    },
    // 国家列表
    async getCountryList () {
      try {
        const res = await getCountry();
        this.countryList = res.data.data.list;
      } catch (error) { }
    },
    async chooseData () {
      try {
        const res = await chooseDataCenter({
          product_id: this.id,
        });
        this.countrySelect = res.data.data.list;
        if (this.countrySelect.length === 1) {
          this.lineForm.country_id = this.countrySelect[0].id;
        }
      } catch (error) { }
    },
    changeType () {
      this.$refs.dataForm.clearValidate(["cloud_config_id"]);
    },
    addData () {
      this.optType = "add";
      this.dataModel = true;
      this.dataForm.country_id = "";
      this.dataForm.city = "";
      this.dataForm.area = "";
      this.dataForm.order = 0;
      this.dataForm.cloud_config = "node";
      this.dataForm.cloud_config_id = "";
      this.comTitle = lang.new_create + lang.data_center;
    },
    async deleteData () {
      try {
        this.submitLoading = true;
        const res = await deleteDataCenter({ id: this.delId });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getDataList();
        this.chooseData();
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    async deleteLine () {
      try {
        this.submitLoading = true;
        const res = await delLine({ id: this.delId });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getDataList();
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    editData (row) {
      this.comTitle = lang.edit + lang.data_center;
      this.optType = "update";
      this.dataModel = true;
      const { id, country_id, city, area, cloud_config, cloud_config_id, order } =
        row;
      this.dataForm = {
        id,
        country_id,
        city,
        area,
        cloud_config,
        cloud_config_id,
        order,
      };
    },
    // 保存数据中心
    async submitData ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.dataForm));
          params.product_id = this.id;
          if (this.optType === "add") {
            delete params.id;
          }
          this.submitLoading = true;
          const res = await createOrUpdateDataCenter(this.optType, params);
          this.$message.success(res.data.msg);
          this.getDataList();
          this.chooseData();
          this.dataModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);

      }
    },
    /* 存储配置 */
    async getStoreList (name) {
      try {
        if (name === "system_disk") {
          this.systemLoading = true;
        } else {
          this.dataLoading = true;
        }
        const res = await getStore(name, {
          product_id: this.id,
          page: 1,
          limit: 1000,
        });
        if (name === "system_disk") {
          this.systemDisk = res.data.data.list;
        } else {
          this.dataDisk = res.data.data.list;
        }
        if (name === "system_disk") {
          this.systemLoading = false;
        } else {
          this.dataLoading = false;
        }
        if (name === "data_disk") {
          this.getDiskTypeList("data_disk");
        }
      } catch (error) {
        this.systemLoading = false;
        this.dataLoading = false;
      }
    },
    async getStoreLimitList (name) {
      try {
        if (name === "system_disk_limit") {
          this.systemLimitLoading = true;
        } else {
          this.diskLimitLoading = true;
        }
        const res = await getStoreLimit(name, {
          product_id: this.id,
          page: 1,
          limit: 1000,
        });
        if (name === "system_disk_limit") {
          this.systemLimitList = res.data.data.list;
        } else {
          this.diskLimitList = res.data.data.list;
        }
        if (name === "system_disk_limit") {
          this.systemLimitLoading = false;
        } else {
          this.diskLimitLoading = false;
        }
      } catch (error) {
        this.systemLimitLoading = false;
        this.diskLimitLoading = false;
      }
    },
    // 修改数据盘新购数量
    async changeDiskLimit (val, type) {
      try {
        if (type === "num") {
          if (val > 16) {
            val = 16;
          }
          if (val === this.tempNum) {
            return;
          }
        }

        const res = await saveDiskNumLimit({
          product_id: this.id,
          disk_limit_switch: this.otherForm.disk_limit_switch,
          disk_limit_num: this.otherForm.disk_limit_num,
        });
        this.$message.success(res.data.msg);
        this.getOtherConfig();
      } catch (error) {
        this.$message.error(error.data.msg);
        this.getOtherConfig();
      }
    },
    // 修改免费数据盘
    async changeFreeDiskLimit (val, type) {
      try {
        if (type === "num") {
          if (val > 1048576) {
            val = 1048576;
          }
          if (val === this.tempFree) {
            return;
          }
        }
        if (!this.otherForm.free_disk_type) {
          this.otherForm.free_disk_type = this.dataDiskType[0]?.value;
        }
        const res = await saveFreeData({
          product_id: this.id,
          free_disk_switch: this.otherForm.free_disk_switch,
          free_disk_size: this.otherForm.free_disk_size,
          free_disk_type: this.otherForm.free_disk_type,
        });
        this.$message.success(res.data.msg);
        this.getOtherConfig();
      } catch (error) {
        this.$message.error(error.data.msg);
        this.getOtherConfig();
      }
    },

    // 切换性能开关
    async changeLimit (val) {
      try {
        const res = await changeCloudSwitch({
          product_id: this.id,
          status: val * 1,
        });
        this.$message.success(res.data.msg);
        this.getOtherConfig();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 删除存储
    async deleteStore (name) {
      try {
        this.submitLoading = true;
        const res = await delStore(name, { id: this.delId });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getStoreList(name);
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    // 删除存储限制
    async deleteStoreLimit (name) {
      try {
        this.submitLoading = true;
        const res = await delStoreLimit(name, { id: this.delId });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getStoreLimitList(name);
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    // 性能提交
    async submitNature ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const {
            id,
            min_value,
            max_value,
            read_bytes,
            write_bytes,
            read_iops,
            write_iops,
          } = this.calcForm;
          const params = {
            id,
            product_id: this.id,
            min_value,
            max_value,
            read_bytes,
            write_bytes,
            read_iops,
            write_iops,
          };
          if (this.optType === "add") {
            delete params.id;
          }
          this.submitLoading = true;
          const res = await createAndUpdateStoreLimit(
            this.calcType,
            this.optType,
            params
          );
          this.$message.success(res.data.msg);
          this.getStoreLimitList(this.calcType);
          this.natureModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);

      }
    },
    /* 存储配置 end*/
    /* 计算配置 */
    async getCpuList () {
      try {
        this.cpuLoading = true;
        const res = await getCpu({
          product_id: this.id,
          page: 1,
          limit: 1000,
        });
        this.cpuList = res.data.data.list;
        this.cpuLoading = false;
      } catch (error) {
        this.cpuLoading = false;
      }
    },
    async getMemoryList () {
      try {
        this.memoryLoading = true;
        const res = await getMemory({
          product_id: this.id,
          page: 1,
          limit: 1000,
        });
        this.memoryList = res.data.data.list;
        this.calcForm.memory_unit = this.memory_unit =
          res.data.data.memory_unit;
        this.memoryLoading = false;
        this.memoryType = lang["mf_" + this.memoryList[0]?.type];
        this.limitMemoryType = this.memoryList[0]?.type || "";
      } catch (error) {
        this.memoryLoading = false;
      }
    },
    addCalc (type) {
      // 添加cpu/memory/system/disk
      this.calcType = type;
      this.optType = "add";
      let temp_type = "",
        memory_unit = "";
      switch (type) {
        case "cpu":
          this.comTitle = `${lang.order_text53}CPU${lang.auth_num}`;
          break;
        case "memory":
          if (this.memoryList.length > 0) {
            this.disabledWay = true;
            temp_type = this.memoryList[0].type;
            memory_unit = this.memory_unit;
          } else {
            this.disabledWay = false;
            memory_unit = "GB";
          }
          this.comTitle = `${lang.order_text53}${lang.memory}`;
          break;
        case "system_disk":
          if (this.systemDisk.length > 0) {
            this.disabledWay = true;
            temp_type = this.systemDisk[0].type;
          } else {
            this.disabledWay = false;
          }
          this.comTitle = `${lang.order_text53}${lang.system_disk_size}`;
          break;
        case "data_disk":
          if (this.dataDisk.length > 0) {
            this.disabledWay = true;
            temp_type = this.dataDisk[0].type;
          } else {
            this.disabledWay = false;
          }
          this.comTitle = `${lang.order_text53}${lang.data_disk}`;
          break;
        case "system_disk_limit":
        case "data_disk_limit":
          this.comTitle = `${lang.order_text53}${lang.disk_limit_enable}`;
          this.natureModel = true;
          this.calcForm = {
            min_value: "",
            max_value: "",
            read_bytes: "",
            write_bytes: "",
            read_iops: "",
            write_iops: "",
          };
          return;
      }
      this.calcModel = true;
      const price = this.cycleData
        .reduce((all, cur) => {
          all.push({
            id: cur.id,
            name: cur.name,
            price: "",
          });
          return all;
        }, [])
        .sort((a, b) => {
          return a.id - b.id;
        });
      this.isAdvance = false;
      this.calcForm = {
        product_id: "",
        cpuValue: "", // cpu里面的value， 提交的时候转换
        price,
        on_demand_price: "",
        other_config: {
          advanced_cpu: "",
          cpu_limit: "",
          ipv6_num: "",
          disk_type: "",
          store_id: "",
        },
        // memory
        type: temp_type,
        value: "",
        min_value: "",
        max_value: "",
        step: "",
        memory_unit: memory_unit,
      };
    },
    // 编辑cpu,memory
    async editCalc (row, type) {
      this.calcType = type;
      this.optType = "update";
      this.disabledWay = true;
      switch (type) {
        case "cpu":
          this.comTitle = `${lang.edit}CPU`;
          this.editCpu(row);
          break;
        case "memory":
          this.comTitle = `${lang.edit}${lang.memory}`;
          this.calcForm.memory_unit = this.memory_unit;
          this.editMemory(row);
          break;
        case "system_disk":
          this.comTitle = `${lang.edit}${lang.system_disk_size}`;
          this.editStore("system_disk", row);
          break;
        case "data_disk":
          this.comTitle = `${lang.edit}${lang.data_disk}`;
          this.editStore("data_disk", row);
          break;
        case "system_disk_limit":
          this.comTitle = `${lang.edit}${lang.disk_limit_enable}`;
          Object.assign(this.calcForm, row);
          this.natureModel = true;
          break;
        case "data_disk_limit":
          this.comTitle = `${lang.edit}${lang.disk_limit_enable}`;
          Object.assign(this.calcForm, row);
          this.natureModel = true;
          break;
      }
      this.isAdvance = false;
    },
    async editCpu (row) {
      try {
        const res = await getCpuDetails({
          id: row.id,
        });
        this.calcModel = true;
        const temp = res.data.data;
        this.calcForm.id = temp.id;
        this.calcForm.cpuValue = temp.value;
        let price = temp.duration
          .reduce((all, cur) => {
            all.push({
              id: cur.id,
              name: cur.name,
              price: cur.price,
            });
            return all;
          }, [])
          .sort((a, b) => {
            return a.id - b.id;
          });
        this.calcForm.id = row.id;
        this.calcForm.price = price;
        this.calcForm.on_demand_price = temp.on_demand_price;
        this.calcForm.other_config = temp.other_config;
        this.optType = "update";
        this.calcModel = true;
        if (
          this.calcForm.other_config.advanced_cpu ||
          this.calcForm.other_config.cpu_limit ||
          this.calcForm.other_config.ipv6_num
        ) {
          this.isAdvance = true;
        }
      } catch (error) { }
    },
    // 编辑内存
    async editMemory (row) {
      try {
        const res = await getMemoryDetails({
          id: row.id,
        });
        this.calcModel = true;
        const temp = res.data.data;
        this.calcForm.id = temp.id;
        this.calcForm.type = temp.type;
        this.calcForm.value = temp.value;
        let price = temp.duration
          .reduce((all, cur) => {
            all.push({
              id: cur.id,
              name: cur.name,
              price: cur.price,
            });
            return all;
          }, [])
          .sort((a, b) => {
            return a.id - b.id;
          });
        this.calcForm.id = row.id;
        this.calcForm.price = price;
        this.calcForm.on_demand_price = temp.on_demand_price;
        this.calcForm.min_value = temp.min_value;
        this.calcForm.max_value = temp.max_value;
        this.calcForm.step = temp.step;
        this.optType = "update";
        this.calcModel = true;
      } catch (error) { }
    },
    // 编辑存储
    async editStore (name, row) {
      try {
        const res = await getStoreDetails(name, {
          id: row.id,
        });
        const temp = res.data.data;
        this.calcForm.id = temp.id;
        this.calcForm.value = temp.value;
        this.calcForm.min_value = temp.min_value;
        this.calcForm.max_value = temp.max_value;
        this.calcForm.step = temp.step;
        this.calcForm.type = temp.type;
        let price = temp.duration
          .reduce((all, cur) => {
            all.push({
              id: cur.id,
              name: cur.name,
              price: cur.price,
            });
            return all;
          }, [])
          .sort((a, b) => {
            return a.id - b.id;
          });
        this.calcForm.price = price;
        this.calcForm.on_demand_price = temp.on_demand_price;
        this.calcForm.other_config = temp.other_config;
        this.optType = "update";
        if (temp.other_config.disk_type || temp.other_config.store_id) {
          this.isAdvance = true;
        }
        this.calcModel = true;
      } catch (error) { }
    },
    submitCalc ({ validateResult, firstError }) {
      if (validateResult === true) {
        switch (this.calcType) {
          case "cpu":
            return this.handlerCpu();
          case "memory":
            return this.handlerMemory();
          case "system_disk":
          case "data_disk":
            return this.handlerStore();
        }
      } else {
        console.log("Errors: ", validateResult);

      }
    },
    async deleteCpu () {
      try {
        this.submitLoading = true;
        const res = await delCpu({
          id: this.delId,
        });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getCpuList();
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    // 提交cpu
    async handlerCpu () {
      try {
        let { id, cpuValue, price, other_config, on_demand_price } = this.calcForm;
        price = price.reduce((all, cur) => {
          cur.price && (all[cur.id] = cur.price);
          return all;
        }, {});
        const params = {
          id,
          product_id: this.id,
          value: cpuValue,
          price,
          other_config,
          on_demand_price
        };
        if (this.optType === "add") {
          delete params.id;
        }
        this.submitLoading = true;
        const res = await createAndUpdateCpu(this.optType, params);
        this.$message.success(res.data.msg);
        this.getCpuList();
        this.calcModel = false;
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    /* 改变最大最小值：内存，系统盘和数据盘
         根据calcType来区分：memory=512， 其他 1048576
          */
    changeMin (e, form) {
      let num =
        this.calcType === "memory"
          ? this.calcForm.memory_unit === "GB"
            ? 512
            : 524288
          : 1048576;
      if (this.subType === "line_bw") {
        num = 30000;
      }
      if (this.subType === "line_ip") {
        num = 10000;
      }
      if (e * 1 >= num) {
        this[form].min_value = 1;
      } else if (e * 1 >= this[form].max_value * 1) {
        if (this[form].max_value * 1) {
          this[form].max_value = e * 1;
        }
      }
    },
    changeMax (e, form) {
      let num =
        this.calcType === "memory"
          ? this.calcForm.memory_unit === "GB"
            ? 512
            : 524288
          : 1048576;
      if (this.subType === "line_bw") {
        num = 30000;
      }
      if (this.subType === "line_ip") {
        num = 10000;
      }
      if (e * 1 > num) {
        this[form].max_value = num;
      } else if (e * 1 <= this[form].min_value * 1) {
        if (this[form].min_value * 1) {
          this[form].min_value = e * 1;
        }
      }
    },
    changeStep (e) {
      if (e * 1 > this.calcForm.max_value * 1 - this.calcForm.min_value * 1) {
        this.calcForm.step = 1;
      }
    },
    async deleteMemory () {
      try {
        this.submitLoading = true;
        const res = await delMemory({
          id: this.delId,
        });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getMemoryList();
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    // 提交内存
    async handlerMemory () {
      try {
        let { id, value, type, price, min_value, max_value, memory_unit, on_demand_price } =
          this.calcForm;
        price = price.reduce((all, cur) => {
          cur.price && (all[cur.id] = cur.price);
          return all;
        }, {});
        const params = {
          id,
          product_id: this.id,
          type,
          value,
          price,
          min_value,
          max_value,
          memory_unit,
          step: 1,
          on_demand_price
        };
        if (this.optType === "add") {
          delete params.id;
        }
        this.submitLoading = true;
        const res = await createAndUpdateMemory(this.optType, params);
        this.$message.success(res.data.msg);
        this.getMemoryList();
        this.calcModel = false;
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    // 提交存储
    async handlerStore () {
      try {
        let { id, value, type, price, min_value, max_value, step, other_config, on_demand_price } =
          this.calcForm;
        price = price.reduce((all, cur) => {
          cur.price && (all[cur.id] = cur.price);
          return all;
        }, {});
        const params = {
          id,
          product_id: this.id,
          type,
          value,
          price,
          min_value,
          max_value,
          step: 1,
          other_config,
          on_demand_price
        };
        if (this.optType === "add") {
          delete params.id;
        }
        this.submitLoading = true;
        const res = await createAndUpdateStore(
          this.calcType,
          this.optType,
          params
        );
        this.$message.success(res.data.msg);
        this.getStoreList(this.calcType);
        this.calcModel = false;
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },

    changeAdvance () {
      this.isAdvance = !this.isAdvance;
    },
    /* 计算配置 end*/
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
        this.getDurationList();
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    closeData () {
      this.dataModel = false;
      this.lineModel = false;
      this.gpuModel = false;
      this.lineType = "";
    },
    async getDurationList () {
      try {
        this.loading = true;
        const res = await getDuration({
          product_id: this.id,
          page: 1,
          limit: 100,
        });
        this.cycleData = res.data.data.list || [];
        this.loading = false;
        this.isShowCycleTip = this.cycleData.some((item) => !item.ratio);
      } catch (error) {
        this.loading = false;
      }
    },
    addCycle () {
      this.optType = "add";
      this.comTitle = lang.add_cycle;
      this.cycleForm.name = "";
      this.cycleForm.unit = "month";
      this.cycleForm.num = "";
      this.cycleForm.price_factor = 1;
      this.cycleForm.price = null;
      this.cycleModel = true;
    },
    editCycle (row) {
      this.optType = "update";
      this.comTitle = lang.update + lang.cycle;
      this.cycleForm = JSON.parse(JSON.stringify(row));
      this.cycleModel = true;
      if (this.cycleForm.price) {
        this.cycleForm.price = this.cycleForm.price * 1;
      }
    },
    async submitCycle ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.cycleForm));
          params.product_id = this.id;
          if (this.optType === "add") {
            delete params.id;
          }
          if (!params.price_factor && params.price_factor !== 0) {
            params.price_factor = "1.00";
          }
          this.submitLoading = true;
          const res = await createAndUpdateDuration(this.optType, params);
          this.$message.success(res.data.msg);
          this.getDurationList();
          this.cycleModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);

      }
    },
    // 删除周期
    async deleteCycle () {
      try {
        this.submitLoading = true;
        const res = await delDuration({
          product_id: this.id,
          id: this.delId,
        });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getDurationList();
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    /* 操作系统 */
    rehandleSelectChange (value, { selectedRowData }) {
      this.selectedRowKeys = value;
    },
    // 批量删除
    deleteBatch () {
      if (this.selectedRowKeys.length == 0) {
        this.$message.warning(lang.mf_tip43);
        return;
      }
      this.delType = "batchSystem";
      this.batchDelete = true;
      this.delVisible = true;
      this.delTit = lang.mf_tip44;
    },
    async handlerBatchSystem () {
      try {
        this.submitLoading = true;
        const id = this.selectedRowKeys;
        const res = await batchDelImage({ id });
        this.$message.success(res.data.msg);
        this.getSystemList();
        this.delVisible = false;
        this.submitLoading = false;
        this.batchDelete = false;
      } catch (error) {
        this.delVisible = false;
        this.submitLoading = false;
        this.$message.error(error.data.msg);
        this.batchDelete = false;
      }
    },
    clearKey () {
      this.systemParams.page = 1;
      this.getSystemList();
    },
    async goToMarket () {
      try {
        const res = await cloudCreateToken();
        let url = res.data.market_url;
        let getqyinfo = url.split("?")[1];
        let getqys = new URLSearchParams("?" + getqyinfo);
        const from = getqys.get("from");
        const token = getqys.get("token");
        window.open(
          `https://my.idcsmart.com/shop/image_market.html?from=${from}&token=${token}`
        );
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 系统列表
    async getSystemList () {
      try {
        this.loading = true;
        const params = JSON.parse(JSON.stringify(this.systemParams));
        if (this.tabs === 'system') {
          params.is_market = this.imageType === 'public' ? 0 : 1;
        }
        params.product_id = this.id;
        const res = await getImage(params);
        this.systemList = res.data.data.list;
        this.loading = false;
        this.selectedRowKeys = [];
      } catch (error) {
        this.loading = false;
      }
    },
    // 系统分类
    async getGroup () {
      try {
        const res = await getImageGroup({
          product_id: this.id,
          orderby: "id",
          sort: "desc",
        });
        this.systemGroup = res.data.data.list;
      } catch (error) { }
    },
    createNewSys () {
      if (this.isAgent) {
        return;
      }
      // 新增
      this.systemModel = true;
      this.optType = "add";
      this.comTitle = `${lang.add}${lang.system}`;
      this.createSystem.image_group_id = "";
      this.createSystem.name = "";
      this.createSystem.charge = 0;
      this.createSystem.price = "";
      this.createSystem.enable = 0;
      this.createSystem.rel_image_id = "";
    },
    editSystem (row) {
      this.optType = "update";
      this.comTitle = lang.update + lang.system;
      this.createSystem = { ...row };
      this.systemModel = true;
    },
    async submitSystem ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.createSystem));
          params.product_id = this.id;
          if (this.optType === "add") {
            delete params.id;
          }
          this.submitLoading = true;
          const res = await createAndUpdateImage(this.optType, params);
          this.$message.success(res.data.msg);
          this.getSystemList();
          this.systemModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);

      }
    },
    // 列表修改状态
    async changeSystemStatus (row) {
      try {
        const params = JSON.parse(JSON.stringify(row));
        params.product_id = this.id;
        const res = await createAndUpdateImage("update", params);
        this.$message.success(res.data.msg);
        this.getSystemList();
      } catch (error) { }
    },
    // 分类管理
    classManage () {
      this.classModel = true;
      this.classParams.name = "";
      this.classParams.icon = "";
      this.optType = "add";
    },
    async submitSystemGroup ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.classParams));
          if (this.optType === "add") {
            delete params.id;
            params.product_id = this.id;
          }
          this.submitLoading = true;
          const res = await createAndUpdateImageGroup(this.optType, params);
          this.$message.success(res.data.msg);
          this.getGroup();
          this.submitLoading = false;
          this.classParams.name = "";
          this.classParams.icon = "";
          this.$refs.classForm.reset();
          this.optType = "add";
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);

      }
    },
    editGroup (row) {
      this.optType = "update";
      this.classParams = JSON.parse(JSON.stringify(row));
    },
    async deleteGroup () {
      try {
        this.submitLoading = true;
        const res = await delImageGroup({
          id: this.delId,
        });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getGroup();
        this.classParams.name = "";
        this.classParams.icon = "";
        this.$refs.classForm.reset();
        this.optType = "add";
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    async deleteSystem () {
      try {
        this.submitLoading = true;
        const res = await delImage({
          id: this.delId,
        });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getSystemList();
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    /* 其他设置 */
    async getOtherConfig () {
      try {
        const res = await getCloudConfig({
          product_id: this.id,
        });
        const temp = res.data.data;
        temp.support_normal_network = Boolean(temp.support_normal_network);
        temp.support_vpc_network = Boolean(temp.support_vpc_network);
        // 处理快照备份的数据
        this.backList = temp.backup_data.map((item) => {
          item.status = false;
          item.price = item.price * 1;
          return item;
        });
        if (temp.backup_data.length === 0) {
          this.otherForm.backup_enable = 0;
        }
        // 处理快照数据
        this.snapList = temp.snap_data.map((item) => {
          item.status = false;
          item.price = item.price * 1;
          return item;
        });
        if (temp.snap_data.length === 0) {
          this.otherForm.snap_enable = 0;
        }
        // 处理资源包数据
        this.resourceList = temp.resource_package.map((item) => {
          item.status = false;
          return item;
        });
        this.tempNum = this.otherForm.disk_limit_num;
        this.tempFree = this.otherForm.free_disk_size;
        this.tempRangeLimit = this.otherForm.disk_range_limit;
        // 默认允许公网IP
        temp.support_public_ip = 1;
        this.otherForm = temp;
        if (!this.isInit) {
          this.store_limit = temp.disk_limit_enable * 1;
        }
        this.isInit = false;
        this.defenceForm.sync_firewall_rule = temp.sync_firewall_rule;
        this.defenceForm.order_default_defence =
          temp.order_default_defence || "";
        this.isLocalSystem = temp.manual_manage === 1;
        this.otherForm.default_nat_acl =
          this.otherForm.default_nat_acl === 0 ? false : true;
        this.otherForm.default_nat_web =
          this.otherForm.default_nat_web === 0 ? false : true;
        this.otherForm.manual_manage =
          this.otherForm.manual_manage === 0 ? false : true;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    changeLenth (e) {
      if (e - this.otherForm.host_prefix.length > 25) {
        this.otherForm.host_length = 25 - this.otherForm.host_prefix.length;
      } else if (e * 1 + this.otherForm.host_prefix.length * 1 < 6) {
        this.otherForm.host_length = 6 - this.otherForm.host_prefix.length;
      }
    },
    addGroup (type) {
      const temp = {
        num: 1,
        type: type,
        price: 0.0,
        status: true, // 编辑状态
      };
      this.backAllStatus = true;
      if (type === "backup") {
        this.backList.push(temp);
      } else if (type === "snap") {
        this.snapList.push(temp);
      } else if (type === "resource") {
        this.resourceList.push({
          rid: "",
          name: "",
          status: true,
        });
      }
    },
    // 添加资源包
    addResourece () {
      this.resourceList.push({
        rid: "",
        name: "",
        status: true, // 编辑状态
      });
    },
    openEdit (type, index) {
      this.backAllStatus = true;
      if (type === "backup") {
        this.backList[index].status = true;
      } else if (type === "snap") {
        this.snapList[index].status = true;
      } else if (type === "resource") {
        this.resourceList[index].status = true;
      }
    },
    closeEdit (row, index, type) {
      if (row.id) {
        // 取消已有数据的编辑
        if (type === "backup") {
          this.backList[index].status = false;
        } else if (type === "snap") {
          this.snapList[index].status = false;
        } else if (type === "resource") {
          this.resourceList[index].status = false;
        }
      } else {
        // 新增未加入数据库的
        if (type === "backup") {
          this.backList.splice(index, 1);
        } else if (type === "snap") {
          this.snapList.splice(index, 1);
        } else if (type === "resource") {
          this.resourceList.splice(index, 1);
        }
      }
      this.backAllStatus = false;
    },

    // 删除 备份/快照
    deleteBackup (type, index) {
      if (type === "backup") {
        this.backList.splice(index, 1);
      } else if (type === "snap") {
        this.snapList.splice(index, 1);
      } else if (type === "resource") {
        this.resourceList.splice(index, 1);
      }
    },
    async submitConfig ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          // 检测是否需要清空数据
          this.submitLoading = true;
          const clearRes = await checkType({
            product_id: this.id,
            type: this.otherForm.type,
          });
          if (clearRes.data.data.clear) {
            this.showConfirm = true;
            this.deleteTip = clearRes.data.data.desc;
            this.submitLoading = false;
          } else {
            this.handlerConfig();
          }
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);

      }
    },
    async submitOntral ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          // 检测是否需要清空数据
          this.submitLoading = true;
          const res = await apiSavePayOnTrial({
            id: this.id,
            pay_ontrial: { ...this.payOntrialForm },
          });
          this.submitLoading = false;
          this.$message.success(res.data.msg);
          this.getServerInfo();
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);

      }
    },
    async handlerConfig () {
      try {
        const params = JSON.parse(JSON.stringify(this.otherForm));
        params.product_id = this.id;
        params.backup_data = this.backList;
        params.snap_data = this.snapList;
        params.resource_package = this.resourceList;
        params.support_normal_network = params.support_normal_network ? 1 : 0;
        params.support_vpc_network = params.support_vpc_network ? 1 : 0;
        params.default_nat_acl = params.default_nat_acl ? 1 : 0;
        params.default_nat_web = params.default_nat_web ? 1 : 0;
        params.manual_manage = params.manual_manage ? 1 : 0;
        if (!params.support_normal_network && !params.support_vpc_network) {
          this.submitLoading = false;
          return this.$message.warning(`${lang.select}${lang.net_type}`);
        }
        const res = await saveCloudConfig(params);
        this.$message.success(res.data.msg);
        this.submitLoading = false;
        this.dataModel = false;
        this.showConfirm = false;
        this.getOtherConfig();
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },

    editGpu (item) {
      this.gpuModel = true;
      this.editGpuId = item.id;
      this.getGpuDetail();
    },
    getGpuDetail (id = this.editGpuId, needFresh = false) {
      apiDataCenterDeta({ id }).then((res) => {
        this.gpuForm.gpu_data = res.data.data.gpu_data;
        this.gpuForm.gpu_name = needFresh
          ? this.gpuForm.gpu_name
          : res.data.data.gpu_name;

        this.gpuForm.city = res.data.data.city;
        this.gpuForm.area = res.data.data.area;
      });
    },

    async submitGpu ({ validateResult, firstError }) {
      if (validateResult === true) {
        this.submitLoading = true;
        try {
          const params = {
            id: this.editGpuId,
            gpu_name: this.gpuForm.gpu_name,
          };
          const res = await apiEditGpuName(params);
          this.$message.success(res.data.msg);
          this.getDataList();
          this.lineRight = false;
          this.gpuModel = false;
          this.lineType = "";
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {

      }
    },
    delGpu (item) {
      if (this.isAgent) {
        return;
      }
      this.delId = item.id;
      this.delTit = lang.sureDelete;
      this.delType = "gpu";
      this.delVisible = true;
    },
    async sureDelGpu () {
      try {
        this.submitLoading = true;
        const res = await apiDelDataCenterGpu({ id: this.delId });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.submitLoading = false;
        this.getDataList();
        this.chooseData();
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    addGpuSub () {
      this.gpuForm.id = "";
      this.gpuForm.value = "";
      this.gpuForm.price = this.handlerAddPrice();
      this.lineRight = true;
      this.isShowLinePrice = true;
    },
    editGpuItem (row) {
      apiGpuDetail({ id: row.id }).then((res) => {
        this.gpuForm.id = res.data.data.id;
        this.gpuForm.value = res.data.data.value;
        this.gpuForm.price = res.data.data.duration;
        this.gpuForm.on_demand_price = res.data.data.on_demand_price;
        this.lineRight = true;
        this.isShowLinePrice = true;
      });
    },
    delGpuItem (row) {
      if (this.isAgent) {
        return;
      }
      this.delId = row.id;
      this.delTit = lang.sureDelete;
      this.delType = "gpu_item";
      this.delVisible = true;
    },
    async sureDelGpuItem () {
      try {
        this.submitLoading = true;
        const res = await apiDelGpu({ id: this.delId });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.submitLoading = false;
        this.getGpuDetail();
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    async submitGpuSub ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const params = JSON.parse(JSON.stringify(this.gpuForm));
          this.submitLoading = true;
          params.price = params.price.reduce((all, cur) => {
            cur.price && (all[cur.id] = cur.price);
            return all;
          }, {});
          const api = params.id ? apiEditGpu : apiAddGpu;
          if (!params.id) {
            params.id = this.editGpuId;
          }
          const res = await api(params);
          this.$message.success(res.data.msg);
          this.submitLoading = false;
          this.getGpuDetail(this.editGpuId, true);
          this.lineRight = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);

      }
    },
    /* 通用删除按钮 */
    comDel (type, row, index, ipType) {
      if (this.isAgent) {
        return;
      }
      this.ipType = ipType;
      this.batchDelete = false;
      this.delId = row.id;
      if (type === "cycle") {
        this.delTit = lang.sure_del_cycle;
      }
      this.delTit = lang.sureDelete;
      this.delType = type;
      // 新增的时候，本地删除线路子项
      if (
        this.lineType === "add" &&
        (this.subType === "line_bw" ||
          this.subType === "line_flow" ||
          this.subType === "line_defence" ||
          this.subType === "line_ip" ||
          this.subType === "line_gpu" ||
          this.subType === "line_flow_on_demand"
        )
      ) {
        this.delSubIndex = index;
        this.delSubItem();
        return;
      }
      this.delVisible = true;
    },
    /* 安全组配置 */
    // 获取安全组配置列表
    async getSecurityGroupList() {
      try {
        this.securityGroupLoading = true;
        const res = await getSecurityGroupConfigList({
          product_id: this.id,
        });
        this.securityGroupList = res.data.data.list || [];
        this.securityGroupLoading = false;
      } catch (error) {
        this.securityGroupLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    // 添加安全组配置
    addSecurityGroup() {
      this.optType = "add";
      this.comTitle = `${lang.add}${lang.security_group_config}`;
      this.securityGroupForm = {
        product_id: this.id,
        description: "",
        protocol: "",
        port: "",
      };
      this.securityGroupModel = true;
    },
    // 编辑安全组配置
    editSecurityGroup(row) {
      if (this.isAgent) {
        return;
      }
      this.optType = "edit";
      this.comTitle = `${lang.edit}${lang.security_group_config}`;
      // 设置标志位，避免回填时触发watch
      this.isEditingSecurityGroup = true;
      this.securityGroupForm = {
        id: row.id,
        product_id: this.id,
        description: row.description,
        protocol: row.protocol,
        port: row.port,
      };
      this.securityGroupModel = true;
      // 在下一个tick重置标志位，这样后续切换协议时watch会正常触发
      this.$nextTick(() => {
        this.isEditingSecurityGroup = false;
      });
    },
    // 提交安全组配置
    async submitSecurityGroup({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          this.submitLoading = true;
          const params = { ...this.securityGroupForm };
          let res;
          if (this.optType === "add") {
            res = await addSecurityGroupConfig(params);
          } else {
            res = await editSecurityGroupConfig(params);
          }
          this.submitLoading = false;
          this.$message.success(res.data.msg);
          this.securityGroupModel = false;
          this.getSecurityGroupList();
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Validate Errors: ", firstError, validateResult);
      }
    },
    // 删除安全组配置
    async deleteSecurityGroup() {
      try {
        const res = await deleteSecurityGroupConfig({
          id: this.delId,
        });
        this.$message.success(res.data.msg);
        this.delVisible = false;
        this.getSecurityGroupList();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 重置安全组配置
    async resetSecurityGroup() {
      try {
        const res = await resetSecurityGroupConfig({
          product_id: this.id,
        });
        this.$message.success(res.data.msg);
        this.getSecurityGroupList();
        this.resetSecurityGroupVisible = false;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 打开重置安全组确认框
    confirmResetSecurityGroup() {
      this.resetSecurityGroupVisible = true;
    },
    // 拖拽排序
    async onSecurityGroupDragSort({ targetIndex, newData }) {
      try {
        this.securityGroupList = newData;
        const ids = newData.map(item => item.id);
        const res = await sortSecurityGroupConfig({
          ids: ids,
        });
        this.$message.success(res.data.msg);
        this.getSecurityGroupList();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 通用删除
    sureDelete () {
      switch (this.delType) {
        case "cycle":
          return this.deleteCycle();
        case "c_cpu":
          return this.deleteCpu();
        case "memory":
          return this.deleteMemory();
        case "system": // 删除镜像
          return this.deleteSystem();
        case "group": // 删除镜像分类
          return this.deleteGroup();
        case "system_disk":
          return this.deleteStore("system_disk");
        case "data_disk":
          return this.deleteStore("data_disk");
        case "system_disk_limit":
          return this.deleteStoreLimit("system_disk_limit");
        case "data_disk_limit":
          return this.deleteStoreLimit("data_disk_limit");
        case "data":
          return this.deleteData();
        case "c_line":
          return this.deleteLine();
        case "line_bw":
        case "line_flow":
        case "line_flow_on_demand":
        case "line_defence":
        case "line_ip":
        case "line_gpu":
          return this.delSubItem();
        case "recommend":
          return this.delRecommend();
        case "limit":
          return this.delLimit();
        case "batchSystem":
          return this.handlerBatchSystem();
        case "gpu":
          return this.sureDelGpu();
        case "gpu_item":
          return this.sureDelGpuItem();
        case "rule_item":
          return this.sureDelRuleItem();
        case "close_firewall":
          return this.closeFirewall();
        case "close_line_firewall":
          return this.closeLineFirewall();
        case "security_group":
          return this.deleteSecurityGroup();
        default:
          return null;
      }
    },
    formatPrice (val) {
      return (val * 1).toFixed(2);
    },
  },
  created () {
    this.id = this.getQuery("id");
    this.iconSelecet = this.iconList.reduce((all, cur) => {
      all.push({
        value: cur,
        label: `${this.host}/plugins/server/mf_cloud/template/admin/img/${cur}.svg`,
      });
      return all;
    }, []);
    this.diskColumns = JSON.parse(JSON.stringify(this.systemDiskColumns));
    this.diskColumns[0].title = `${lang.data_disk}（GB）`;
    // 默认拉取数据
    this.getPlugin();
    this.getServerInfo();
    this.getDurationList();
    this.getOtherConfig();
    this.backNatureColumns = JSON.parse(JSON.stringify(this.natureColumns));
  },
}).$mount(template);

const { showToast, showLoadingToast, closeToast, showImagePreview } = vant;
window.lang = Object.assign(window.lang, window.module_lang);
const app2 = Vue.createApp({
  components: {
    vanSelect,
    discountCode,
    eventCode,
    customGoods,
  },
  data() {
    return {
      lang: window.lang,
      finance_login: false,
      hasDiscount: false,
      isShowFull: false,
      isShowLevel: false,
      showConfigPage: false,
      editDiskIndex: 0,
      id: "",
      tit: "",
      commonData: {},
      product_related_list: [],
      eventData: {
        id: "",
        discount: 0,
      },
      showSystemDisk: false,
      calcTotalPrice: 0,
      showFast: true,
      self_defined_field: {},
      activeName: "fast", // fast, custom
      country: "",
      countryName: "",
      city: "",
      curImage: 0,
      imageName: "",
      version: "",
      curImageId: "",
      showImgPick: false,
      dataList: [], // 数据中心
      resourceList: [], // 资源包
      ressourceName: "",
      baseConfig: {},
      cpuList: [], //cpu
      gpuList: [],
      gpu_name: "",
      memoryList: [], // 内存
      memory_arr: [], // 范围时内存数组
      memMarks: {},
      bwMarks: {},
      memoryTip: "",
      limitList: [], // 限制
      packageId: "", // 套餐ID
      imageList: [], // 镜像
      marketImageCount: 0,
      filterIamge: [],
      systemDiskList: [], // 系统盘
      systemDiskTip: "",
      dataDiskList: [], // 数据盘
      configLimitList: [], // 限制规则
      cloudIndex: 0,
      cycle: "", // 周期
      cycleList: [],
      qty: 1,
      recommendList: [], // 推荐套餐
      // 区域
      area_name: "",
      isChangeArea: true,
      lineList: [], // 线路
      lineType: "",
      lineDetail: {}, // 线路详情：bill_type, flow, bw, defence , ip
      lineName: "",
      bwName: "",
      defenseName: "",
      cpuName: "",
      memoryName: "",
      bwArr: [],
      bwTip: "",
      ipv4Tip: "",
      ipv4Arr: [],
      ipv6Tip: "",
      ipv6Arr: [],
      params: {
        // 配置参数
        data_center_id: "",
        cpu: "",
        memory: 1,
        image_id: 0,
        system_disk: {
          size: "",
          disk_type: "",
        },
        data_disk: [],
        backup_num: "",
        snap_num: "",
        line_id: "",
        bw: "",
        flow: "",
        peak_defence: "",
        ip_num: "",
        ipv6_num: "",
        duration_id: "",
        network_type: "",
        // 提交购买
        name: "", // 主机名
        ssh_key_id: "",
        /* 安全组 */
        security_group_id: "",
        security_group_protocol: [],
        password: "",
        re_password: "",
        vpc: {
          // 新建-系统分配的时候都不传
          id: "", // 选择已有的vc
          ips: "", // 自定义的时候
        },
        port: null,
        notes: "",
        auto_renew: false,
        // 资源包
        resource_package_id: "",
        ip_mac_bind_enable: false, // 嵌套虚拟化
        nat_acl_limit_enable: false, // NAT转发
        nat_web_limit_enable: false, // NAT建站
        ipv6_num_enable: false, // IPv6
      },
      plan_way: 0,
      hover: false,
      login_way: lang.auto_create, // 登录方式 auto_create
      rules: {
        data_center_id: [
          { required: true, message: "请输入活动名称", trigger: "blur" },
        ],
        name: [
          {
            pattern: /^[A-Za-z][a-zA-Z0-9_.-]{5,24}$/,
            message: lang.mf_tip16,
          },
        ],
      },
      sshList: [],
      dis_visible: false,
      // 配置价格
      loadingPrice: true,
      totalPrice: 0.0,
      preview: [],
      sonPreview: [],
      discount: "",
      duration: "",
      /* 优惠码 */
      promo: {
        scene: "new",
        promo_code: "",
        billing_cycle_time: "",
        event_promotion: "",
      },
      cartDialog: false,
      isInit: true,
      memoryType: false,
      /* 拖动内存 */
      mStep: 1,
      mMin: "",

      mMax: "",
      isShowRecommend: false,
      clickIndex: 0,
      /* 存储 */
      storeList: [],
      systemType: [],
      dataType: [],
      systemNum: [],
      dataNumObj: {},
      systemRangArr: {}, // 系统盘不同类型的取值范围数组
      systemRangTip: {}, // 系统盘不同类型的取值范围提示
      dataRangArr: {}, // 数据盘不同类型的取值范围数组
      dataRangTip: {}, // 数据盘不同类型的取值范围提示
      //验证密码
      hasLen: false,
      hasAppoint: true, // 只能输入
      hasLine: false,
      hasMust: false, // 必须包含必须包含小写字母a~z，大写字母A~Z,字母0-9
      /* 安全组 */
      groupName: lang.no_safe_group,
      groupList: [],
      planWayOptions: [
        {
          id: 0,
          name: lang.auto_plan,
        },
        {
          id: 1,
          name: lang.custom,
        },
      ],
      isShowImage: false,
      groupSelect: [
        { value: "icmp", name: lang.icmp_name, check: true },
        { value: "ssh", name: lang.ssh_name, check: true },
        { value: "rdp", name: lang.rdp_name, check: true },
        { value: "http", name: lang.http_name, check: true },
        { value: "https", name: lang.https_name, check: true },
        { value: "telnet", name: lang.telnet_name, check: true },
        { value: "udp_53", name: lang.udp_name, check: true },
      ],
      /* 网络类型 */
      netName: "",
      /* vpc */
      vpcList: [],
      vpc_ips: {
        vpc1: {
          tips: lang.range1,
          value: 10,
          select: [
            {
              text: 10,
              value: 10,
            },
            {
              text: 172,
              value: 172,
            },
            {
              text: 192,
              value: 192,
            },
          ],
        },
        vpc2: 0,
        vpc3: 0,
        vpc3Tips: "",
        vpc4: 0,
        vpc4Tips: "",
        vpc6: {
          value: 16,
          select: [
            {
              text: 16,
              value: 16,
            },
            {
              text: 17,
              value: 17,
            },
            {
              text: 18,
              value: 18,
            },
            {
              text: 19,
              value: 19,
            },
            {
              text: 20,
              value: 20,
            },
            {
              text: 21,
              value: 21,
            },
            {
              text: 22,
              value: 22,
            },
            {
              text: 23,
              value: 23,
            },
            {
              text: 24,
              value: 24,
            },
            {
              text: 25,
              value: 25,
            },
            {
              text: 26,
              value: 26,
            },
            {
              text: 27,
              value: 27,
            },
            {
              text: 28,
              value: 28,
            },
          ],
        },
        min: 0,
        max: 255,
      },
      limitNum: 0,
      // 回调相关
      isUpdate: false,
      isConfig: false,
      position: 0,
      backfill: {},
      isLogin: false,
      showErr: false,
      sshLoading: false,
      groupLoading: false,
      vpcLoading: false,
      showImage: false,
      showSsh: false,
      showPas: false,
      showRepass: false,
      isHide: true,
      levelNum: 0,
      isCustom: false,
      isManual: false, // 手动切换
      isChangeMemory: false,
      freeDataRange: [],
      // ssh端口设置
      ssh_port_type: 0, // 0=默认,1=随机端口,2=指定端口
      diskStep: 1,
      imageType: "public"
    };
  },
  mounted() {
    this.getConfig();
    this.hasDiscount = havePlugin("PromoCode");
    this.isShowLevel = havePlugin("IdcsmartClientLevel");
    // 开启活动满减
    this.isShowFull = havePlugin("EventPromotion");
    // 监听子页面想父页面的传参
    window.addEventListener("message", (event) => this.submitOrder(event));
  },
  updated() {
    this.calcFreeDataDiskTip();
    this.isShowBtn = true;
    if (this.activeName === "fast") {
      return;
    }
  },
  created() {
    this.isLogin = localStorage.getItem("jwt");
    this.getCommonData();
    // 回显配置
    let temp = {};
    const params = getUrlParams();
    this.id = params.id;
    this.getGoodsName();
    this.getIamgeList();
    if (params.config || sessionStorage.getItem("product_information")) {
      try {
        temp = JSON.parse(params.config);
        this.isUpdate = true;
        this.isConfig = true;
      } catch (e) {
        temp = JSON.parse(sessionStorage.getItem("product_information")) || {};
        this.isUpdate = params.change;
      }
    }
    if (this.isUpdate && temp.config_options) {
      this.backfill = temp.config_options;
      temp.config_options.auto_renew = temp.config_options.auto_renew
        ? true
        : false;
      temp.config_options.ip_mac_bind_enable = temp.config_options
        .ip_mac_bind_enable
        ? true
        : false;
      temp.config_options.nat_acl_limit_enable = temp.config_options
        .nat_acl_limit_enable
        ? true
        : false;
      temp.config_options.nat_web_limit_enable = temp.config_options
        .nat_web_limit_enable
        ? true
        : false;
      temp.config_options.ipv6_num_enable = temp.config_options.ipv6_num_enable
        ? true
        : false;
      this.isChangeArea = false;
      const {
        country,
        countryName,
        city,
        curImage,
        version,
        curImageId,
        cloudIndex,
        activeName,
        imageName,
        network_type,
        defenseName,
        security_group_id,
        security_group_protocol,
        login_way,
        recommend_config_id,
        groupName,
        data_center_id,
      } = this.backfill;
      this.packageId = recommend_config_id;
      this.promo = temp.customfield;
      this.self_defined_field = temp.self_defined_field || {};
      this.qty = temp.qty;
      this.position = temp.position;
      this.activeName = activeName;
      this.country = country;
      this.countryName = countryName;
      this.curImage = curImage;
      this.city = city;
      this.version = version;
      this.curImageId = curImageId;
      this.cloudIndex = cloudIndex;
      this.imageName = imageName;
      this.groupName = groupName;
      this.netName = network_type === "vpc" ? lang.mf_vpc : lang.mf_normal;
      if (network_type === "vpc") {
        this.getVpcList(data_center_id);
      }
      this.params.vpc.id = temp.config_options.vpc.id;
      const ips = temp.config_options.vpc.ips;
      this.plan_way = ips ? 1 : 0;
      if (ips) {
        const arr = ips.split("/");
        const arr1 = arr[0].split(".");
        this.vpc_ips.vpc1.value = arr1[0] * 1;
        this.vpc_ips.vpc2 = arr1[1] * 1;
        this.vpc_ips.vpc3 = arr1[2] * 1;
        this.vpc_ips.vpc4 = arr1[3] * 1;
        this.vpc_ips.vpc6.value = arr[1] * 1;
      }
      this.defenseName = defenseName;

      // 安全组
      if (security_group_id) {
        this.groupName = lang.exist_group;
        this.getGroup();
      } else {
        this.groupName = lang.create_group;
      }
      if (security_group_protocol.length > 0) {
        this.groupSelect = this.groupSelect.map((item) => {
          if (security_group_protocol.includes(item.value)) {
            item.check = true;
          }
          return item;
        });
      }
      // 登录方式
      this.login_way = login_way;
      if (login_way === lang.security_tab1) {
        this.getSsh();
      }
    }
  },
  watch: {
    "params.image_id"(id) {
      if (id) {
        this.showImage = false;
      }
    },
    dis_visible(val) {
      if (!val) {
        this.showErr = false;
      }
    },
    "params.network_type"(type) {
      this.netName = type === "normal" ? lang.mf_normal : lang.mf_vpc;
      if (this.isInit) {
        return;
      }
      if (this.activeName === "fast") {
        return;
      }
      if (type === "vpc") {
        this.params.ipv6_num = "";
      } else {
        this.params.ipv6_num = this.lineDetail.ipv6[0]?.value;
      }
    },
    // 系统盘改变类型，筛选数量可选
    "params.system_disk.disk_type"(val) {
      if (this.activeName === "fast") {
        return;
      }
      // 回填初次不初始化
      if (this.isInit && this.isUpdate) {
        return;
      }
      if (this.systemDiskList[0].type === "radio") {
        this.params.system_disk.size = this.calcSystemDiskList[0].value;
      } else {
        // 使用步长逻辑设置默认值
        const firstStepValue = this.getFirstStepValue(this.calcSystemDiskList);
        this.params.system_disk.size = firstStepValue;
      }
      if (!this.isInit) {
        this.getCycleList();
      }
    },
    "params.line_id"(id) {
      // 区域改变，线路必定改变，根据线路改变拉取线路详情，以及处理cpu,memory,bw/flow
      if (id && this.activeName === "custom") {
        this.lineType = this.lineList.filter(
          (item) => item.id === this.params.line_id
        )[0]?.bill_type;
        this.getLineDetails(id);
      }
    },
    "params.ssh_key_id"(id) {
      if (id) {
        this.showSsh = false;
      }
    },
    vpcIps: {
      handler(newVal) {
        this.params.vpc.ips = newVal;
      },
      immediate: true,
      deep: true,
    },
    "params.cpu"(val) {
      if (val) {
        // this.params.cpu = val * 1;
        this.limitNum++;
      }
    },
    "params.memory"(val) {
      if (val) {
        // this.params.memory = val * 1;
        this.limitNum++;
      }
    },
    "params.data_center_id"(val) {
      this.isChangeMemory = false;
    },
  },
  computed: {
    calcDataSelect() {
      const freeSize = this.baseConfig.free_disk_size;
      const temp = (
        this.dataNumObj[this.baseConfig.free_disk_type] || []
      ).filter((item) => item.value >= freeSize);
      const valurArr = temp.map((item) => item.value);
      if (!valurArr.includes(freeSize)) {
        temp.push({
          value: freeSize,
          label: freeSize,
        });
      }
      return temp.sort((a, b) => a.value - b.value);
    },
    calcFreeDataDiskTip() {
      let temp = (
        this.dataRangArr[this.baseConfig.free_disk_type] || []
      ).filter((item) => item >= this.baseConfig.free_disk_size * 1);
      if (!temp.includes(this.baseConfig.free_disk_size)) {
        temp.push(this.baseConfig.free_disk_size);
      }
      this.freeDataRange = temp.sort((a, b) => a - b) || [];
      if (temp.length === 0) {
        return `${this.baseConfig.free_disk_size}-${this.baseConfig.free_disk_size}`;
      }
      return this.createTip(temp);
    },
    /* 最新限制 */
    // 系统盘
    calcSystemDiskList() {
      const originSystemArr =
        this.systemRangArr[this.params.system_disk.disk_type];
      if (this.activeName === "fast") {
        return;
      }
      if (this.systemDiskList.length === 0) {
        return [];
      }
      if (this.configLimitList.length === 0) {
        if (this.systemDiskList[0].type === "radio") {
          return this.systemDiskList.filter(
            (item) =>
              item.other_config.disk_type === this.params.system_disk.disk_type
          );
        } else {
          this.systemDiskTip = this.createTip(originSystemArr);
          return originSystemArr;
        }
      }
      let temp = this.configLimitList
        .reduce((all, cur) => {
          if (cur.result.system_disk) {
            all.push(cur);
          }
          return all;
        }, [])
        .filter((item) => {
          return (
            (!item.rule.data_center ||
              (item.rule.data_center.opt === "eq"
                ? item.rule.data_center.id.includes(this.params.data_center_id)
                : !item.rule.data_center.id.includes(
                  this.params.data_center_id
                ))) &&
            (!item.rule.cpu ||
              (item.rule.cpu.opt === "eq"
                ? item.rule.cpu.value.includes(this.params.cpu)
                : !item.rule.cpu.value.includes(this.params.cpu))) &&
            (!item.rule.memory ||
              (item.rule.memory.opt === "eq"
                ? this.handleRange(item.rule, "memory")
                : !this.handleRange(item.rule, "memory"))) &&
            (!item.rule.image ||
              (item.rule.image.opt === "eq"
                ? item.rule.image.id.includes(this.params.image_id)
                : !item.rule.image.id.includes(this.params.image_id))) &&
            (!item.rule.bw ||
              (item.rule.bw.opt === "eq"
                ? this.handleBwRange(item.rule.bw)
                : !this.handleBwRange(item.rule.bw))) &&
            (!item.rule.flow ||
              (item.rule.flow.opt === "eq"
                ? this.handleFlowRange(item.rule.flow)
                : !this.handleFlowRange(item.rule.flow))) &&
            (!item.rule.ipv4_num ||
              (item.rule.ipv4_num.opt === "eq"
                ? this.handleIpv4Range(item.rule.ipv4_num)
                : !this.handleIpv4Range(item.rule.ipv4_num)))
          );
        });
      let ruleResult = [];
      if (temp.length === 0) {
        if (this.systemDiskList[0].type === "radio") {
          return this.systemDiskList.filter(
            (item) =>
              item.other_config.disk_type === this.params.system_disk.disk_type
          );
        } else {
          this.systemDiskTip = this.createTip(originSystemArr);
          return originSystemArr;
        }
      } else {
        ruleResult = temp;
      }
      const systemMax = originSystemArr[originSystemArr.length - 1];
      let system_arr = ruleResult.reduce((all, cur) => {
        // 根据 eq,neq 判断是否取反
        let rangeArr = [],
          min = "",
          max = "";
        rangeArr = Array.from(
          new Set(
            cur.result.system_disk.reduce((sum, pre) => {
              sum.push(pre.min, pre.max);
              return sum;
            }, [])
          )
        ).sort((a, b) => a - b);
        min = rangeArr[0] === "" ? rangeArr[1] : rangeArr[0];
        max =
          rangeArr[rangeArr.length - 1] > systemMax
            ? systemMax
            : rangeArr[rangeArr.length - 1];
        if (cur.result.system_disk[0].opt === "eq") {
          // 内部求并集
          let _temp = [];
          cur.result.system_disk.forEach((m) => {
            _temp.push(
              ...this.createArr([
                m.min * 1,
                m.max === "" ? systemMax : m.max * 1,
              ])
            );
          });
          all.push(_temp);
        } else {
          let result = [],
            _temp = [];
          cur.result.system_disk.forEach((m) => {
            _temp.push(
              ...this.createArr([
                m.min * 1,
                m.max === "" ? systemMax : m.max * 1,
              ])
            );
          });
          result = originSystemArr.filter((item) => !_temp.includes(item));
          all.push(result);
        }
        return all;
      }, []);
      let filterSystem = [];
      let systemOpt = this.handleMixed(...system_arr);
      if (systemOpt.length === 0) {
        // 取交集数据为空的时候
        systemOpt = originSystemArr;
      }
      if (this.systemDiskList[0].type === "radio") {
        const temp = originSystemArr.filter((item) => systemOpt.includes(item));
        filterSystem = this.systemDiskList.filter((item) =>
          temp.includes(item.value)
        );
        if (filterSystem.length === 0) {
          filterSystem = this.systemDiskList.filter(
            (item) =>
              item.other_config.disk_type === this.params.system_disk.disk_type
          );
        }
      } else {
        filterSystem = systemOpt.filter((item) =>
          originSystemArr.includes(item)
        );
        if (filterSystem.length === 0) {
          filterSystem = originSystemArr;
        }
        this.systemDiskTip = this.createTip(filterSystem);
      }
      // 当前无之前选项重置
      if (this.systemDiskList[0].type === "radio") {
        const systemId = filterSystem.map((item) => item.value * 1);
        if (!systemId.includes(this.params.system_disk.size)) {
          this.params.system_disk.size = systemId[0];
        }
      } else {
        if (!filterSystem.includes(this.params.system_disk.size)) {
          // 使用步长逻辑设置默认值
          const firstStepValue = this.getFirstStepValue(filterSystem);
          this.params.system_disk.size = firstStepValue;
        }
      }
      return filterSystem;
    },
    // 根据限制处理周期
    calcDuration() {
      if (this.activeName === "fast") {
      }
    },
    /* 最新限制 end */
    showFlowBw() {
      if (!this.lineDetail.flow && this.lineDetail.bill_type !== "bw") {
        return;
      }
      // return this.lineDetail.flow.filter(
      //   (item) => item.value === this.params.flow
      // )[0]?.other_config?.out_bw;
      const taregt = "flow";
      let temp =
        this.lineDetail[taregt].filter(
          (item) => item.value === this.params.flow
        )[0]?.bw || [];
      this.params.bw = temp[0]?.out_bw;
      temp = JSON.parse(JSON.stringify(temp)).map((item) => {
        return {
          value: item.out_bw,
          text: item.out_bw === 0 ? lang.not_limited : item.out_bw + "M",
        };
      });
      return temp;
    },
    showPort() {
      return this.baseConfig.rand_ssh_port === 1 && this.params.port === "";
    },
    root_name() {
      return this.imageName.indexOf("Win") !== -1 ? "administrator" : "root";
    },
    calcArea() {
      const c = this.dataList.filter((item) => item.id === this.country * 1)[0]
        ?.name;
      return c + this.city;
    },
    calcAreaList() {
      // 计算区域列表
      if (this.activeName === "fast" || this.isCustom) {
        return;
      }
      const temp =
        this.dataList
          .filter((item) => item.id === this.country * 1)[0]
          ?.city.filter((item) => item.name === this.city)[0]?.area || [];

      if (!this.isChangeArea) {
        return temp;
      }

      this.area_name = temp[0]?.name;
      this.lineList = temp[0]?.line || [];
      this.gpuList = temp[0]?.gpu || [];
      this.gpu_name = temp[0]?.gpu_name || "";
      this.params.gpu_num = temp[0]?.gpu[0]?.value;
      this.params.data_center_id = this.lineList[0]?.data_center_id;
      this.params.line_id = this.lineList[0]?.id;
      this.lineName = this.lineList[0]?.name;
      // 区域变化, 重置cpu, 内存
      this.params.cpu = this.calcCpuList[0]?.value;
      if (!this.isChangeMemory) {
        if (this.memoryList[0]?.type === "radio") {
          this.params.memory = this.calaMemoryList[0]?.value * 1;
        } else {
          this.params.memory = this.calaMemoryList[0] * 1;
        }
      }
      if (!this.baseConfig.support_normal_network) {
        this.getVpcList();
      }
      return temp;
    },
    calcCpu() {
      return this.params.cpu + lang.mf_cores;
    },
    vpcIps() {
      if (
        this.vpc_ips.vpc2 !== undefined &&
        this.vpc_ips.vpc3 !== undefined &&
        this.vpc_ips.vpc4 !== undefined
      ) {
        const str =
          this.vpc_ips.vpc1.value +
          "." +
          this.vpc_ips.vpc2 +
          "." +
          this.vpc_ips.vpc3 +
          "." +
          this.vpc_ips.vpc4 +
          "/" +
          this.vpc_ips.vpc6.value;
        return str;
      } else {
        return "";
      }
    },
    calcUsable() {
      return this.dataList
        .filter((item) => item.id === this.country * 1)[0]
        ?.city.filter((item) => item.name === this.city)[0]
        ?.area.filter((item) => item.id === this.params.data_center_id)[0]
        ?.name;
    },
    calcLine() {
      return this.dataList
        .filter((item) => item.id === this.country * 1)[0]
        ?.city.filter((item) => item.name === this.city)[0]
        ?.area.filter((item) => item.id === this.params.data_center_id)[0]
        ?.line.filter((item) => item.id === this.params.line_id)[0]?.name;
    },
    calcCpuList() {
      if (this.activeName === "fast") {
        return;
      }
      if (this.configLimitList.length === 0) {
        if (this.isInit && this.isUpdate) {
        } else {
          this.params.cpu = this.cpuList[0]?.value;
        }
        return this.cpuList;
      }
      // 1.找到结果有关于cpu的限制
      const temp = JSON.parse(JSON.stringify(this.configLimitList))
        .reduce((all, cur) => {
          if (cur.result.cpu) {
            all.push(cur);
          }
          return all;
        }, [])
        .filter(
          // 2.筛选当前配置全部符合条件的限制
          (item) =>
            (!item.rule.data_center ||
              (item.rule.data_center.opt === "eq"
                ? item.rule.data_center?.id.includes(this.params.data_center_id)
                : !item.rule.data_center?.id.includes(
                  this.params.data_center_id
                ))) &&
            (!item.rule.memory ||
              (item.rule.memory.opt === "eq"
                ? this.handleRange(item.rule, "memory")
                : !this.handleRange(item.rule, "memory"))) &&
            (!item.rule.image ||
              (item.rule.image.opt === "eq"
                ? item.rule.image?.id.includes(this.params.image_id)
                : !item.rule.image?.id.includes(this.params.image_id))) &&
            (!item.rule.bw ||
              (item.rule.bw.opt === "eq"
                ? this.handleBwRange(item.rule.bw)
                : !this.handleBwRange(item.rule.bw))) &&
            (!item.rule.flow ||
              (item.rule.flow.opt === "eq"
                ? this.handleFlowRange(item.rule.flow)
                : !this.handleFlowRange(item.rule.flow))) &&
            (!item.rule.ipv4_num ||
              (item.rule.ipv4_num.opt === "eq"
                ? this.handleIpv4Range(item.rule.ipv4_num)
                : !this.handleIpv4Range(item.rule.ipv4_num)))
        );
      let temCpu = [];
      if (temp.length > 0) {
        // 结果求交集
        let cpuArr = temp.reduce((all, cur) => {
          const tempCpu = cur.result.cpu.reduce((sum, pre) => {
            sum.push(...pre.value);
            return sum;
          }, []);
          if (cur.result.cpu[0].opt === "eq") {
            all.push(tempCpu);
          } else {
            const allCpu = this.cpuList.reduce((all, cur) => {
              all.push(cur.value);
              return all;
            }, []);
            const result = allCpu.filter((item) => !tempCpu.includes(item));
            all.push(result);
          }
          return all;
        }, []);
        cpuArr = Array.from(new Set(cpuArr));
        cpuOpt = this.handleMixed(...cpuArr);
        if (cpuOpt.length === 0) {
          // 没有交集的时候取全部
          temCpu = this.cpuList;
        } else {
          temCpu = this.cpuList.filter((item) => {
            return Array.from(new Set(cpuOpt)).includes(item.value);
          });
        }
      } else {
        temCpu = this.cpuList;
      }
      const cpuId = temCpu.map((item) => item.value * 1);
      if (!cpuId.includes(this.params.cpu)) {
        this.params.cpu = cpuId[0];
      }
      return temCpu;
    },
    calaMemoryList() {
      // 计算可选内存，根据 cpu + 区域
      if (this.activeName === "fast") {
        return;
      }
      if (this.configLimitList.length === 0) {
        if (this.memoryList[0]?.type === "radio") {
          return this.memoryList;
        } else {
          this.memoryTip = this.createTip(this.memory_arr);
          this.memMarks = this.createMarks(this.memory_arr); // data 原数据，目标marks
          return this.memory_arr;
        }
      }
      let temp = JSON.parse(JSON.stringify(this.configLimitList))
        .reduce((all, cur) => {
          if (cur.result.memory) {
            all.push(cur);
          }
          return all;
        }, [])
        .filter((item) => {
          return (
            (!item.rule.data_center ||
              (item.rule.data_center.opt === "eq"
                ? item.rule.data_center.id.includes(this.params.data_center_id)
                : !item.rule.data_center.id.includes(
                  this.params.data_center_id
                ))) &&
            (!item.rule.cpu ||
              (item.rule.cpu.opt === "eq"
                ? item.rule.cpu.value.includes(this.params.cpu)
                : !item.rule.cpu.value.includes(this.params.cpu))) &&
            (!item.rule.image ||
              (item.rule.image.opt === "eq"
                ? item.rule.image.id.includes(this.params.image_id)
                : !item.rule.image.id.includes(this.params.image_id))) &&
            (!item.rule.bw ||
              (item.rule.bw.opt === "eq"
                ? this.handleBwRange(item.rule.bw)
                : !this.handleBwRange(item.rule.bw))) &&
            (!item.rule.flow ||
              (item.rule.flow.opt === "eq"
                ? this.handleFlowRange(item.rule.flow)
                : !this.handleFlowRange(item.rule.flow))) &&
            (!item.rule.ipv4_num ||
              (item.rule.ipv4_num.opt === "eq"
                ? this.handleIpv4Range(item.rule.ipv4_num)
                : !this.handleIpv4Range(item.rule.ipv4_num)))
          );
        });

      let ruleResult = [];
      if (temp.length === 0) {
        if (this.memoryList[0]?.type === "radio") {
          return this.memoryList;
        } else {
          this.memoryTip = this.createTip(this.memory_arr);
          this.memMarks = this.createMarks(this.memory_arr); // data 原数据，目标marks
          return this.memory_arr;
        }
      } else {
        ruleResult = temp;
      }
      // 内存原始范围
      let originmemory_arr = [];
      if (this.memoryList[0]?.type === "radio") {
        originmemory_arr = this.memoryList.map((item) => item.value);
      } else {
        this.memoryList.forEach((item) => {
          originmemory_arr.push(
            ...this.createArr([item.min_value, item.max_value])
          );
        });
      }
      // 最小，最大值求交集
      const memoryMax = this.memory_arr[this.memory_arr.length - 1];
      let memory_arr = ruleResult.reduce((all, cur) => {
        // 根据 eq,neq 判断是否取反，结果改为数组，根据内存type来处理
        let radioArr = [],
          rangeArr = [],
          min = "",
          max = "";
        // 单选时的范围
        if (this.memoryList[0]?.type === "radio") {
          radioArr = Array.from(
            new Set(
              cur.result.memory.reduce((sum, pre) => {
                sum.push(...pre.value);
                return sum;
              }, [])
            )
          );
        } else {
          rangeArr = Array.from(
            new Set(
              cur.result.memory.reduce((sum, pre) => {
                sum.push(pre.min, pre.max);
                return sum;
              }, [])
            )
          ).sort((a, b) => a - b);
          min = rangeArr[0] === "" ? rangeArr[1] : rangeArr[0];
          max =
            rangeArr[rangeArr.length - 1] > memoryMax
              ? memoryMax
              : rangeArr[rangeArr.length - 1];
        }
        if (cur.result.memory[0].opt === "eq") {
          if (this.memoryList[0]?.type === "radio") {
            all.push(radioArr);
          } else {
            // 内部求并集
            let _temp = [];
            cur.result.memory.forEach((m) => {
              _temp.push(
                ...this.createArr([
                  m.min * 1,
                  m.max === "" ? memoryMax : m.max * 1,
                ])
              );
            });
            all.push(_temp);
          }
        } else {
          let result = [],
            _temp = [];
          if (this.memoryList[0]?.type === "radio") {
            _temp = radioArr;
          } else {
            cur.result.memory.forEach((m) => {
              _temp.push(
                ...this.createArr([
                  m.min * 1,
                  m.max === "" ? memoryMax : m.max * 1,
                ])
              );
            });
          }
          result = this.memory_arr.filter((item) => !_temp.includes(item));
          all.push(result);
        }
        return all;
      }, []);
      let filterMemory = [];
      let memoryOpt = this.handleMixed(...memory_arr);
      if (memoryOpt.length === 0) {
        // 取交集数据为空的时候
        memoryOpt = this.memory_arr;
      }
      if (this.memoryList[0]?.type === "radio") {
        originmemory_arr = originmemory_arr.filter((item) =>
          memoryOpt.includes(item)
        );
        filterMemory = this.memoryList.filter((item) =>
          originmemory_arr.includes(item.value)
        );
      } else {
        filterMemory = memoryOpt.filter((item) =>
          originmemory_arr.includes(item)
        );
        this.memoryTip = this.createTip(filterMemory);
      }
      // console.log('filterMemory', filterMemory)
      // 当前无之前选项重置
      if (this.memoryList[0]?.type === "radio") {
        const memoryId = filterMemory.map((item) => item.value * 1);
        if (!memoryId.includes(this.params.memory)) {
          this.params.memory = memoryId[0];
        }
      } else {
        if (!filterMemory.includes(this.params.memory)) {
          this.params.memory = filterMemory[0];
        }
      }
      return filterMemory;
    },
    /* bw限制 */
    calcBwList() {
      // 根据区域，线路来判断计算可选带宽  范围
      if (this.lineDetail.bill_type === "flow") {
        return;
      }
      if (!this.lineDetail.bw || this.lineDetail.bw.length === 0) {
        return [];
      }
      // 没有限制条件时，根据类型返回对应数据
      if (this.configLimitList.length === 0) {
        if (this.lineDetail.bw[0]?.type === "radio") {
          return this.lineDetail.bw;
        } else {
          this.bwTip = this.createTip(this.bwArr);
          this.bwMarks = this.createMarks(this.bwArr);
          return this.bwArr || [];
        }
      }
      const temp = JSON.parse(JSON.stringify(this.configLimitList))
        .reduce((all, cur) => {
          if (cur.result.bw) {
            all.push(cur);
          }
          return all;
        }, [])
        .filter(
          (item) =>
            (!item.rule.data_center ||
              (item.rule.data_center.opt === "eq"
                ? item.rule.data_center.id.includes(this.params.data_center_id)
                : !item.rule.data_center.id.includes(
                  this.params.data_center_id
                ))) &&
            (!item.rule.cpu ||
              (item.rule.cpu.opt === "eq"
                ? item.rule.cpu.value.includes(this.params.cpu)
                : !item.rule.cpu.value.includes(this.params.cpu))) &&
            (!item.rule.memory ||
              (item.rule.memory.opt === "eq"
                ? this.handleRange(item.rule, "memory")
                : !this.handleRange(item.rule, "memory"))) &&
            (!item.rule.flow ||
              (item.rule.flow.opt === "eq"
                ? this.handleFlowRange(item.rule.flow)
                : !this.handleFlowRange(item.rule.flow))) &&
            (!item.rule.image ||
              (item.rule.image.opt === "eq"
                ? item.rule.image.id.includes(this.params.image_id)
                : !item.rule.image.id.includes(this.params.image_id))) &&
            (!item.rule.ipv4_num ||
              (item.rule.ipv4_num.opt === "eq"
                ? this.handleIpv4Range(item.rule.ipv4_num)
                : !this.handleIpv4Range(item.rule.ipv4_num)))
        );
      if (temp.length === 0) {
        // 没有匹配到限制条件
        if (this.lineDetail.bw[0]?.type === "radio") {
          return this.lineDetail.bw;
        } else {
          this.bwTip = this.createTip(this.bwArr);
          this.bwMarks = this.createMarks(this.bwArr);
          return this.bwArr || [];
        }
      }
      let fArr = [];
      // radio 类型时，从 lineDetail.bw 提取可选值数组
      const isRadio = this.lineDetail.bw[0]?.type === "radio";
      const radioBwValues = isRadio
        ? this.lineDetail.bw.map((item) => item.value * 1)
        : [];
      const maxBw = isRadio
        ? Math.max(...radioBwValues)
        : this.bwArr[this.bwArr.length - 1];
      const originBwArr = isRadio ? radioBwValues : this.bwArr;

      const bwArr = temp.reduce((all, cur) => {
        let rangeArr = [],
          min = "",
          max = "";
        // dcim限制bw都是范围
        rangeArr = Array.from(
          new Set(
            cur.result.bw.reduce((sum, pre) => {
              sum.push(pre.min, pre.max);
              return sum;
            }, [])
          )
        ).sort((a, b) => a - b);
        min = rangeArr[0] === "" ? rangeArr[1] : rangeArr[0];
        max =
          rangeArr[rangeArr.length - 1] > maxBw
            ? maxBw
            : rangeArr[rangeArr.length - 1];

        if (cur.result.bw[0].opt === "eq") {
          // 内部求并集
          let _temp = [];
          if (isRadio) {
            // radio 类型：筛选在 min~max 范围内的可选值
            cur.result.bw.forEach((m) => {
              const ruleMin = m.min * 1;
              const ruleMax = m.max === "" ? maxBw : m.max * 1;
              radioBwValues.forEach((v) => {
                if (v >= ruleMin && v <= ruleMax) {
                  _temp.push(v);
                }
              });
            });
          } else {
            cur.result.bw.forEach((m) => {
              _temp.push(
                ...this.createArr([m.min * 1, m.max === "" ? maxBw : m.max * 1])
              );
            });
          }
          all.push(_temp);
        } else {
          let result = [];
          if (isRadio) {
            // radio 类型：排除在 min~max 范围内的可选值
            let excludeArr = [];
            cur.result.bw.forEach((m) => {
              const ruleMin = m.min * 1;
              const ruleMax = m.max === "" ? maxBw : m.max * 1;
              radioBwValues.forEach((v) => {
                if (v >= ruleMin && v <= ruleMax) {
                  excludeArr.push(v);
                }
              });
            });
            result = radioBwValues.filter((item) => !excludeArr.includes(item));
          } else {
            cur.result.bw.forEach((m) => {
              result.push(
                ...this.createArr([m.min * 1, m.max === "" ? maxBw : m.max * 1])
              );
            });
            result = this.bwArr.filter((item) => !result.includes(item));
          }
          all.push(result);
        }
        return all;
      }, []);
      // 求交集
      let bwOpt = this.handleMixed(...bwArr);
      if (this.lineDetail.bw[0]?.type === "radio") {
        fArr =
          this.lineDetail.bw.filter((item) => bwOpt.includes(item.value * 1)) ||
          [];
        if (fArr.length === 0) {
          fArr = this.lineDetail.bw;
        }
      } else {
        fArr = Array.from(new Set(bwOpt)).sort((a, b) => a - b);
        if (fArr.length === 0) {
          fArr = this.bwArr;
        }
        fArr = bwOpt.filter((item) => this.bwArr.includes(item));
        this.bwTip = this.createTip(fArr);
        this.bwMarks = this.createMarks(fArr);
      }
      let bwId = [];

      if (this.lineDetail.bw[0]?.type === "radio") {
        bwId = fArr.map((item) => item.value * 1);
      } else {
        bwId = fArr;
      }
      bwId = bwId.map((item) => {
        if (isNaN(item)) {
          item = "NC";
        }
        return item;
      });
      if (bwId.length > 0 && !bwId.includes(this.params.bw)) {
        this.params.bw = bwId[0];
        // 同步更新 bwName，确保 radio 选中状态正确
        this.bwName = bwId[0] + "M";
      }
      return fArr;
    },
    /* flow限制 */
    calcFlowList() {
      if (this.lineDetail.bill_type === "bw") {
        return [];
      }
      if (this.configLimitList.length === 0) {
        return this.lineDetail.flow;
      }
      const temp = JSON.parse(JSON.stringify(this.configLimitList))
        .reduce((all, cur) => {
          if (cur.result.flow) {
            all.push(cur);
          }
          return all;
        }, [])
        .filter(
          (item) =>
            (!item.rule.data_center ||
              (item.rule.data_center.opt === "eq"
                ? item.rule.data_center.id.includes(this.params.data_center_id)
                : !item.rule.data_center.id.includes(
                  this.params.data_center_id
                ))) &&
            (!item.rule.cpu ||
              (item.rule.cpu.opt === "eq"
                ? item.rule.cpu.value.includes(this.params.cpu)
                : !item.rule.cpu.value.includes(this.params.cpu))) &&
            (!item.rule.memory ||
              (item.rule.memory.opt === "eq"
                ? this.handleRange(item.rule, "memory")
                : !this.handleRange(item.rule, "memory"))) &&
            (!item.rule.bw ||
              (item.rule.bw.opt === "eq"
                ? this.handleBwRange(item.rule.bw)
                : !this.handleBwRange(item.rule.bw))) &&
            (!item.rule.image ||
              (item.rule.image.opt === "eq"
                ? item.rule.image.id.includes(this.params.image_id)
                : !item.rule.image.id.includes(this.params.image_id))) &&
            (!item.rule.ipv4_num ||
              (item.rule.ipv4_num.opt === "eq"
                ? this.handleIpv4Range(item.rule.ipv4_num)
                : !this.handleIpv4Range(item.rule.ipv4_num)))
        );
      let fArr = [];
      if (temp.length > 0) {
        const maxFlow = this.lineDetail.flow
          .map((item) => item.value * 1)
          .sort((a, b) => a - b);
        // 结果求交集
        const flowArr = temp.reduce((all, cur) => {
          let rangeArr = Array.from(
            new Set(
              cur.result.flow.reduce((sum, pre) => {
                sum.push(pre.min, pre.max);
                return sum;
              }, [])
            )
          ).sort((a, b) => a - b);
          const min = rangeArr[0] === "" ? rangeArr[1] : rangeArr[0];
          const max =
            rangeArr[rangeArr.length - 1] > maxFlow[maxFlow.length - 1]
              ? maxFlow[maxFlow.length - 1]
              : rangeArr[rangeArr.length - 1];
          if (cur.result.flow[0].opt === "eq") {
            let _temp = [];
            cur.result.flow.forEach((m) => {
              _temp.push(
                ...this.createArr([m.min * 1, m.max === "" ? max : m.max * 1])
              );
            });
            all.push(_temp);
          } else {
            let result = [],
              _temp = [];
            cur.result.flow.forEach((m) => {
              _temp.push(
                ...this.createArr([m.min * 1, m.max === "" ? max : m.max * 1])
              );
            });
            result = maxFlow.filter((item) => !_temp.includes(item));
            all.push(result);
          }
          return all;
        }, []);
        const flowOpt = this.handleMixed(...flowArr);
        if (flowOpt.length === 0) {
          fArr = this.lineDetail.flow;
        } else {
          fArr = this.lineDetail.flow.filter((item) =>
            flowOpt.includes(item.value * 1)
          );
        }
      } else {
        fArr = this.lineDetail.flow;
      }
      const flowId = fArr.map((item) => item.value * 1);
      if (!flowId.includes(this.params.flow)) {
        this.params.flow = flowId[0];
        // 同步更新 flowName，确保选中状态正确
        if (this.params.billing_cycle === "on_demand") {
          this.flowName = lang.mf_demand_fee;
        } else {
          this.flowName = flowId[0] > 0 ? flowId[0] + "G" : lang.mf_tip28;
        }
      }
      return fArr;
    },
    /* ipv4限制 */
    calcIpv4List() {
      // 根据数据中心、CPU、内存、带宽、流量、操作系统来计算可选的 IP 范围
      if (!this.lineDetail.ip || this.lineDetail.ip.length === 0) {
        return [];
      }
      const isRadio = this.lineDetail.ip[0]?.type === "radio";
      if (this.configLimitList.length === 0) {
        if (isRadio) {
          return this.lineDetail.ip;
        } else {
          this.ipv4Tip = this.createTip(this.ipv4Arr);
          return this.ipv4Arr || [];
        }
      }
      const temp = JSON.parse(JSON.stringify(this.configLimitList))
        .reduce((all, cur) => {
          if (cur.result.ipv4_num) {
            all.push(cur);
          }
          return all;
        }, [])
        .filter(
          (item) =>
            (!item.rule.data_center ||
              (item.rule.data_center.opt === "eq"
                ? item.rule.data_center.id.includes(this.params.data_center_id)
                : !item.rule.data_center.id.includes(
                  this.params.data_center_id
                ))) &&
            (!item.rule.cpu ||
              (item.rule.cpu.opt === "eq"
                ? item.rule.cpu.value.includes(this.params.cpu)
                : !item.rule.cpu.value.includes(this.params.cpu))) &&
            (!item.rule.memory ||
              (item.rule.memory.opt === "eq"
                ? this.handleRange(item.rule, "memory")
                : !this.handleRange(item.rule, "memory"))) &&
            (!item.rule.bw ||
              (item.rule.bw.opt === "eq"
                ? this.handleBwRange(item.rule.bw)
                : !this.handleBwRange(item.rule.bw))) &&
            (!item.rule.flow ||
              (item.rule.flow.opt === "eq"
                ? this.handleFlowRange(item.rule.flow)
                : !this.handleFlowRange(item.rule.flow))) &&
            (!item.rule.image ||
              (item.rule.image.opt === "eq"
                ? item.rule.image.id.includes(this.params.image_id)
                : !item.rule.image.id.includes(this.params.image_id)))
        );

      if (temp.length === 0) {
        if (isRadio) {
          return this.lineDetail.ip;
        } else {
          this.ipv4Tip = this.createTip(this.ipv4Arr);
          return this.ipv4Arr || [];
        }
      }
      let fArr = [];
      if (isRadio) {
        // radio 类型：根据 num 字段筛选
        const ipArr = temp.reduce((all, cur) => {
          if (cur.result.ipv4_num[0].opt === "eq") {
            // eq: 求并集
            let _temp = [];
            cur.result.ipv4_num.forEach((m) => {
              const min = m.min === "" ? 0 : m.min * 1;
              const max = m.max === "" ? Infinity : m.max * 1;
              this.lineDetail.ip.forEach((item) => {
                const num = String(item.value).split("_")[0];
                const numValue = num === "NC" ? "NC" : num * 1;
                if (numValue !== "NC" && numValue >= min && numValue <= max) {
                  _temp.push(item.value);
                }
              });
            });
            all.push(_temp);
          } else {
            // neq: 排除
            let excludeValues = [];
            cur.result.ipv4_num.forEach((m) => {
              const min = m.min === "" ? 0 : m.min * 1;
              const max = m.max === "" ? Infinity : m.max * 1;
              this.lineDetail.ip.forEach((item) => {
                const num = String(item.value).split("_")[0];
                const numValue = num === "NC" ? "NC" : num * 1;
                if (numValue !== "NC" && numValue >= min && numValue <= max) {
                  excludeValues.push(item.value);
                }
              });
            });
            const result = this.lineDetail.ip
              .filter((item) => !excludeValues.includes(item.value))
              .map((item) => item.value);
            all.push(result);
          }
          return all;
        }, []);
        // 求交集
        let resultValues = this.handleMixed(...ipArr);
        if (resultValues.length === 0) {
          fArr = this.lineDetail.ip;
        } else {
          fArr = this.lineDetail.ip.filter((item) =>
            resultValues.includes(item.value)
          );
        }
        // 如果当前选中的 ip_num 不在可选范围内，自动选择第一个
        const validValues = fArr.map((item) => item.value);
        if (!validValues.includes(this.params.ip_num)) {
          this.params.ip_num = fArr.length > 0 ? fArr[0].value : 0;
        }
      } else {
        // slider 类型：计算范围
        const maxIp = this.ipv4Arr[this.ipv4Arr.length - 1];
        const ipRangeArr = temp.reduce((all, cur) => {
          if (cur.result.ipv4_num[0].opt === "eq") {
            let _temp = [];
            cur.result.ipv4_num.forEach((m) => {
              _temp.push(
                ...this.createArr([
                  m.min * 1,
                  m.max === "" ? maxIp : m.max * 1,
                ])
              );
            });
            all.push(_temp);
          } else {
            let result = [];
            cur.result.ipv4_num.forEach((m) => {
              result.push(
                ...this.createArr([
                  m.min * 1,
                  m.max === "" ? maxIp : m.max * 1,
                ])
              );
            });
            result = this.ipv4Arr.filter((item) => !result.includes(item));
            all.push(result);
          }
          return all;
        }, []);
        // 求交集
        let ipOpt = this.handleMixed(...ipRangeArr);
        fArr = Array.from(new Set(ipOpt)).sort((a, b) => a - b);
        if (fArr.length === 0) {
          fArr = this.ipv4Arr;
        }
        fArr = ipOpt.filter((item) => this.ipv4Arr.includes(item));
        this.ipv4Tip = this.createTip(fArr);
        // 不在这里重置值，由 changeIpNum 处理跳转逻辑
      }
      return fArr;
    },
    calcCartName() {
      return this.isUpdate ? lang.product_sure_check : lang.product_add_cart;
    },
    calcDataNum() {
      return this.params.data_disk.reduce((all, cur) => {
        all += cur.size;
        return all;
      }, 0);
    },
    calcImageList() {
      let temp = JSON.parse(JSON.stringify(this.imageList));
      if (temp.length === 0) {
        return [];
      }
      /* 限制只针对自定义，不支持套餐 */
      if (
        this.activeName === "custom" &&
        this.limitNum &&
        this.configLimitList.length > 0
      ) {
        let tempLimit = JSON.parse(JSON.stringify(this.configLimitList))
          .reduce((all, cur) => {
            if (cur.result.image) {
              all.push(cur);
            }
            return all;
          }, [])
          .filter(
            (item) =>
              (!item.rule.data_center ||
                (item.rule.data_center.opt === "eq"
                  ? item.rule.data_center.id.includes(
                    this.params.data_center_id
                  )
                  : !item.rule.data_center.id.includes(
                    this.params.data_center_id
                  ))) &&
              (!item.rule.cpu ||
                (item.rule.cpu.opt === "eq"
                  ? item.rule.cpu.value.includes(this.params.cpu)
                  : !item.rule.cpu.value.includes(this.params.cpu))) &&
              (!item.rule.memory ||
                (item.rule.memory.opt === "eq"
                  ? this.handleRange(item.rule, "memory")
                  : !this.handleRange(item.rule, "memory"))) &&
              (!item.rule.bw ||
                (item.rule.bw.opt === "eq"
                  ? this.handleBwRange(item.rule.bw)
                  : !this.handleBwRange(item.rule.bw))) &&
              (!item.rule.flow ||
                (item.rule.flow.opt === "eq"
                  ? this.handleFlowRange(item.rule.flow)
                  : !this.handleFlowRange(item.rule.flow))) &&
              (!item.rule.ipv4_num ||
                (item.rule.ipv4_num.opt === "eq"
                  ? this.handleIpv4Range(item.rule.ipv4_num)
                  : !this.handleIpv4Range(item.rule.ipv4_num)))
          );
        const allImageId = this.imageList.reduce((all, cur) => {
          all.push(...cur.image.map((item) => item.id));
          return all;
        }, []);
        const imageId = tempLimit.reduce((all, cur) => {
          // 改版过后同规则多条数据求并集
          const tempImage = cur.result.image.reduce((sum, pre) => {
            sum.push(...pre.id);
            return sum;
          }, []);
          if (cur.result.image[0].opt === "eq") {
            all.push(tempImage);
          } else {
            let result = allImageId.filter((item) => !tempImage.includes(item));
            all.push(result);
          }
          return all;
        }, []);
        // 求交集
        let resultImage = this.handleMixed(...imageId);
        if (resultImage.length === 0) {
          resultImage = allImageId;
        }
        if (tempLimit.length > 0) {
          temp = temp
            .map((item) => {
              item.image = item.image.filter((el) =>
                resultImage.includes(el.id)
              );
              return item;
            })
            .filter((item) => item.image.length > 0);
          // image_id 不在可选配置内
          const imageId = temp.reduce((all, cur) => {
            all.push(...cur.image.map((item) => item.id));
            return all;
          }, []);
          if (
            (!this.params.image_id ||
              !imageId.includes(this.params.image_id)) &&
            temp.length > 0
          ) {
            this.curImageId = temp[0]?.id;
            this.imageName = temp[0].image[0]?.name;
            this.curImage = 0;
            this.version = temp[0].image[0]?.name;
            this.params.image_id = temp[0].image[0]?.id;
            this.isManual = false;
          }
        }
      }
      this.filterIamge = temp;
      return temp;
    },
    calcNetTypeColumns() {
      const arr = [];
      if (this.baseConfig.support_normal_network) {
        arr.push({
          text: lang.mf_normal,
          value: "normal",
        });
      }
      if (
        this.activeName === "fast" &&
        this.baseConfig.support_vpc_network &&
        !this.params.ipv6_num
      ) {
        arr.push({
          text: lang.mf_vpc,
          value: "vpc",
        });
      }
      if (this.activeName === "custom" && this.baseConfig.support_vpc_network) {
        arr.push({
          text: lang.mf_vpc,
          value: "vpc",
        });
      }
      return arr;
    },
    calcVpcList() {
      return [{ name: lang.create_network, id: "" }].concat(this.vpcList);
    },
    calcOsImgList() {
      const temp =
        this.calcImageList.filter((item) => item.id === this.curImageId)[0]
          ?.image || [];
      return temp;
    },
    calcLoginWayList() {
      const arr = [
        {
          value: lang.set_pas,
        },
        {
          value: lang.auto_create,
        },
      ];
      // if (
      //   this.baseConfig.support_ssh_key &&
      //   this.imageName.indexOf("Win") === -1
      // ) {
      //   arr.unshift({ value: lang.security_tab1 });
      // }
      /* 不保存产品密码的时候不能设置密码 */
      if (this.commonData.donot_save_client_product_password === 1) {
        arr = arr.filter(item => item.value !== lang.set_pas)
      }
      return arr;
    },
  },
  methods: {
    // 返回产品列表页
    changeFlowBw() {
      this.bwName =
        this.params.bw === 0 ? lang.not_limited : this.params.bw + "M";
      this.getCycleList();
    },
    goBack() {
      window.history.back();
    },
    formateTime(time) {
      if (time && time !== 0) {
        return formateDate(time * 1000);
      } else {
        return "--";
      }
    },
    filterMoney(money) {
      if (isNaN(money) || money * 1 < 0) {
        return "0.00";
      } else {
        return formatNuberFiexd(money);
      }
    },
    deepCopy(obj, hash = new WeakMap()) {
      if (typeof obj !== "object" || obj === null) {
        return obj;
      }
      if (hash.has(obj)) {
        return hash.get(obj);
      }

      let copy = Array.isArray(obj) ? [] : {};
      hash.set(obj, copy);

      for (let key in obj) {
        if (obj.hasOwnProperty(key)) {
          copy[key] = this.deepCopy(obj[key], hash);
        }
      }

      return copy;
    },
    // 处理结果的交集
    handleMixed(...arr) {
      if (arr.length === 0) {
        return [];
      }
      let resultArr = new Set(arr[0]);
      for (let i = 1; i < arr.length; i++) {
        const curArr = arr[i];
        if (!curArr || !curArr.length) {
          return [];
        }
        const newArr = new Set();
        for (const element of resultArr) {
          if (curArr.includes(element)) {
            newArr.add(element);
          }
        }
        resultArr = newArr;
        if (resultArr.size === 0) {
          return [];
        }
      }
      return Array.from(resultArr);
    },
    handleRange(item, type) {
      // 处理范围内的是否包含当前参数: memory,system_disk,data_disk,bw,flow,ipv4_num,ipv6_num
      // 初始化的时候，需要处理各参数的最大范围
      let target = "";
      if (type === "system_disk") {
        target = this.params.system_disk.size;
      } else if (type === "data_disk") {
        // 当没有选择数据盘的时候， 值为 "", 会过滤掉设置了数据盘的规则
        target = this.params.data_disk[0]?.size;
        if (!target) {
          return true;
        }
      } else {
        target = this.params[type];
      }
      let rangeMax = this[`${type}_arr`][this[`${type}_arr`].length - 1];
      let tempArr = [];
      // 内存 改为单选和范围
      if (item[type].value) {
        // value 是离散值数组，直接检查是否包含
        return item[type].value.includes(target * 1);
      } else {
        tempArr = [
          item[type].min * 1,
          item[type].max === ""
            ? rangeMax
            : item[type].max * 1 >= rangeMax
              ? rangeMax
              : item[type].max * 1,
        ];
      }
      return this.createArr(tempArr).includes(target);
    },
    handleBwRange(bwRule) {
      // 处理带宽范围判断
      // 如果当前是流量线路（没有带宽），bw 为 null/undefined/空字符串，不应该匹配带宽条件
      if (this.params.bw === null || this.params.bw === undefined || this.params.bw === "") {
        return false;
      }
      const target = this.params.bw * 1;
      if (isNaN(target)) {
        return false;
      }
      const min = bwRule.min === "" ? 0 : bwRule.min * 1;
      const max = bwRule.max === "" ? Infinity : bwRule.max * 1;
      return target >= min && target <= max;
    },
    handleFlowRange(flowRule) {
      // 处理流量范围判断
      // 如果当前是带宽线路（没有流量），flow 为 null/undefined/空字符串，不应该匹配流量条件
      if (this.params.flow === null || this.params.flow === undefined || this.params.flow === "") {
        return false;
      }
      const target = this.params.flow * 1;
      if (isNaN(target)) {
        return false;
      }
      const min = flowRule.min === "" ? 0 : flowRule.min * 1;
      const max = flowRule.max === "" ? Infinity : flowRule.max * 1;
      return target >= min && target <= max;
    },
    handleIpv4Range(ipv4Rule) {
      // 处理 ipv4_num 范围判断
      const target = this.params.ip_num * 1;
      if (isNaN(target)) {
        return false;
      }
      const min = ipv4Rule.min === "" ? 0 : ipv4Rule.min * 1;
      const max = ipv4Rule.max === "" ? Infinity : ipv4Rule.max * 1;
      return target >= min && target <= max;
    },
    changeIpv4() {
      // 看有没有关联ipv4的限制
      this.getCycleList();
    },
    changeIpv6() {
      // 看有没有关联ipv6的限制
      this.getCycleList();
    },
    clickRecommend(index) {
      this.clickIndex = index;
    },
    handleRecomend() {
      this.clickIndex = this.cloudIndex;
      this.isShowRecommend = true;
    },
    changeNat(e) {
      if (e) {
        this.params.ipv6_num_enable = false;
      }
    },
    getQuery(name) {
      const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
      const r = window.location.search.substr(1).match(reg);
      if (r != null) return decodeURI(r[2]);
      return null;
    },
    // 随机端口
    randomNum() {
      const min = this.baseConfig.rand_ssh_port_start * 1;
      const max = this.baseConfig.rand_ssh_port_end * 1;
      const range = max - min + 1;
      const num = Math.floor(Math.random() * range) + min;
      return num;
    },
    refreshPort() {
      this.params.port = this.randomNum();
    },
    // 配置数据
    async getConfig() {
      try {
        const params = {
          id: this.id,
        };
        if (this.activeName === "fast") {
          params.scene = "recommend";
        } else {
          this.isCustom = true;
        }
        const res = await getOrderConfig(params);
        const temp = res.data.data;
        // 通用数据处理
        this.dataList = temp.data_center;
        this.resourceList = temp.resource_package || [];
        this.baseConfig = temp.config;
        // 初始化磁盘步长
        this.diskStep = (this.baseConfig.disk_range_limit_switch && this.baseConfig.disk_range_limit) ?
          this.baseConfig.disk_range_limit : 1;
        // 如果没有推荐配置，跳转到自定义，重新获取数据
        if (this.dataList.length === 0) {
          if (this.activeName === "fast") {
            this.activeName = "custom";
            this.showFast = false;
            this.getConfig();
            return;
          } else {
            showToast(lang.product_conig_tip);
          }
        }
        // 初始化数据
        if (!this.isUpdate) {
          // 不是回填
          this.params = {
            data_center_id: "",
            cpu: "",
            memory: 1,
            image_id: this.imageList[0]?.image[0]?.id,
            system_disk: {
              size: "",
              disk_type: "",
            },
            data_disk: [],
            backup_num: "",
            snap_num: "",
            line_id: "",
            bw: "",
            flow: "",
            peak_defence: "",
            ip_num: "",
            ipv6_num: "",
            duration_id: "",
            network_type:
              this.baseConfig.type !== "host"
                ? "normal"
                : this.baseConfig.support_normal_network
                  ? "normal"
                  : "vpc",
            name: "",
            ssh_key_id: "",
            security_group_id: "",
            security_group_protocol: [],
            password: "",
            re_password: "",
            vpc: {
              id: "",
              ips: "",
            },
            port: null,
            notes: "",
            auto_renew: false,
            resource_package_id: this.resourceList[0]?.id || "",
            ip_mac_bind_enable: false,
            nat_acl_limit_enable: false,
            nat_web_limit_enable: false,
            ipv6_num_enable: false,
          };
          this.qty = 1;
          this.ressourceName = this.resourceList[0]?.name;
          this.country = String(this.dataList[0]?.id);
          this.countryName = String(this.dataList[0]?.name);
          this.city = String(this.dataList[0]?.city[0]?.name);
          this.cloudIndex = 0;
          this.plan_way = 0;
          this.login_way = lang.auto_create;
          this.createPassword();
          this.isCustom = false;
          /* 根据后台设置的默认 nat 开关 + vpc 等条件判断选中 */
          if (
            this.params.network_type === "vpc" ||
            (this.params.network_type === "normal" &&
              this.baseConfig.type === "lightHost")
          ) {
            if (this.baseConfig.default_nat_acl) {
              this.params.nat_acl_limit_enable = true;
            }
            if (this.baseConfig.default_nat_web) {
              this.params.nat_web_limit_enable = true;
            }
          }
          if (this.baseConfig.rand_ssh_port === 1) {
            this.refreshPort();
          }
          if (this.baseConfig.rand_ssh_port === 3) {
            this.params.port = 22;
          }
        } else {
          // 回填数据
          this.params = this.backfill;
          // if (this.baseConfig.free_disk_switch) {
          //   // 免费数据盘
          //   this.params.data_disk.splice(0, 0, {
          //     disk_type: "",
          //     size: this.baseConfig.free_disk_size,
          //   });
          // }
          this.ressourceName = this.resourceList.filter(
            (item) => item.id === this.params.resource_package_id
          )[0]?.name;
        }

        this.totalPrice = 0.0;
        this.isInit = true;
        // 保存cpu,memory,system_disk,data_disk,config_limit
        this.cpuList = temp.cpu;
        this.memoryList = temp.memory;
        if (temp.memory.length > 0) {
          if (temp.memory[0].type !== "radio") {
            this.memoryType = false;
            // 范围的时候生成默认范围数组
            this.memory_arr = temp.memory.reduce((all, cur) => {
              all.push(...this.createArr([cur.min_value, cur.max_value]));
              return all;
            }, []);
          } else {
            this.memoryType = true;
            this.memory_arr = temp.memory.map((item) => item.value);
          }
        }
        this.memory_arr = this.memory_arr.sort((a, b) => a - b);
        this.systemDiskList = temp.system_disk;
        this.dataDiskList = temp.data_disk;
        this.configLimitList = temp.limit_rule;
        // 处理存储
        this.handlerType(temp.system_disk, "system");
        this.handlerType(temp.data_disk, "data");
        // 专业版获取后台安全组
        if ((this.commonData?.edition || 0) * 1) {
          const tempSafe = temp.security_group_config.map(item => {
            return {
              value: item.id,
              name: item.description,
              check: true
            }
          })
          tempSafe.push({
            value: "remote_port", name: lang.safe_auto_config, check: true
          })
          this.groupSelect = tempSafe;
        }
        // fast 推荐配置
        if (this.activeName === "fast") {
          this.handlerFast();
        } else {
          this.isCustom = false;
          this.handlerCustom();
        }
      } catch (error) {
        console.log("@@@", error);
      }
    },
    // 处理套餐配置
    async handlerFast() {
      if (this.activeName === "custom") {
        return;
      }
      const temp = this.dataList
        .filter((item) => item.id === this.country * 1)[0]
        ?.city.filter((item) => item.name === this.city)[0]
        ?.area.reduce((all, cur) => {
          all.push(...cur.recommend_config);
          return all;
        }, []);
      this.recommendList = temp;
      const res = await getLineDetail({ id: this.id, line_id: temp[0].line_id });
      this.lineDetail = res.data.data;
      // 初始化套餐数据
      if (!this.isUpdate) {
        this.packageId = temp[0].id;
        this.params.data_center_id = temp[0].data_center_id;
        this.params.cpu = temp[0].cpu;
        this.params.gpu_num = temp[0].gpu_num;
        this.gpu_name = temp[0].gpu_name;
        this.params.memory = temp[0].memory * 1 || 0;
        this.params.line_id = temp[0].line_id;
        this.lineType = temp[0].bill_type;
        this.params.bw = temp[0].bw;
        this.params.flow = temp[0].flow;
        this.params.ipv6_num = temp[0].ipv6_num;
        this.params.system_disk.size = temp[0].system_disk_size;
        this.params.system_disk.disk_type = temp[0].system_disk_type;
        if (this.lineDetail.defence && this.lineDetail.defence.length > 0) {
          this.params.peak_defence = this.lineDetail.defence.find(
            (item) => item.value == this.lineDetail.order_default_defence
          )?.value;
          this.defenseName = this.lineDetail.defence.find(
            (item) => item.value == this.params.peak_defence
          )?.desc;
        }

        if (temp[0].data_disk_size * 1) {
          this.params.data_disk = [];
          this.params.data_disk.push({
            size: temp[0].data_disk_size,
            disk_type: temp[0].data_disk_type,
          });
        } else {
          this.params.data_disk = [];
        }
      } else {
        this.gpu_name = temp.filter(
          (item) => item.gpu_num === this.params.gpu_num
        )[0]?.gpu_name;
        this.lineType = temp.filter(
          (item) => item.id === this.params.recommend_config_id
        )[0]?.bill_type;
      }
      // 计算价格
      this.getCycleList();
    },
    // 切换自定义配置
    handlerCustom() {
      if (this.baseConfig.only_sale_recommend_config === 1) {
        // 仅购买套餐
        return;
      }
      if (!this.isUpdate) {
        this.createPassword();
        this.storeList = [];
        // 默认第一个系统盘类型
        this.params.system_disk.disk_type = this.systemType[0].value;
        // 使用步长逻辑设置默认大小
        if (this.systemDiskList[0].type === "radio") {
          this.params.system_disk.size = this.systemDiskList[0].value;
        } else {
          const firstStepValue = this.getFirstStepValue(this.calcSystemDiskList);
          this.params.system_disk.size = firstStepValue;
        }
        if (this.systemDiskList[0].type === "radio") {
          // 单选
          this.systemNum = this.systemDiskList
            .filter(
              (item) =>
                item.other_config.disk_type ===
                this.params.system_disk.disk_type
            )
            .reduce((all, cur) => {
              all.push({
                value: cur.value,
                label: cur.value,
              });
              return all;
            }, []);
        }
        // 根据类型确定最大最小值
        this.storeList.push({
          type: this.systemDiskList[0].type,
          name: lang.mf_system,
          disk_type: this.systemType[0].value || "",
          disk_text: this.systemType[0].label,
          size:
            this.systemDiskList[0].value || this.systemDiskList[0].min_value,
          min: this.systemDiskList[0].min_value,
          max: this.systemDiskList[this.systemDiskList.length - 1].max_value,
        });
        // 如果有免费数据盘
        if (this.baseConfig.free_disk_switch) {
          let max = this.baseConfig.free_disk_size;
          if (this.dataDiskList[0].type !== "radio") {
            const temp = this.dataDiskList
              .filter(
                (item) =>
                  item.other_config.disk_type === this.baseConfig.free_disk_type
              )
              .reduce((all, cur) => {
                all.push(cur.min_value, cur.max_value);
                return all;
              }, [])
              .sort((a, b) => a - b);
            if (max < temp[temp.length - 1]) {
              max = temp[temp.length - 1];
            }
          }

          this.storeList.push({
            min: this.baseConfig.free_disk_size,
            max,
            type: this.dataDiskList[0].type,
            name: lang.mf_tip37,
            disk_type: this.baseConfig.free_disk_type,
            size: this.baseConfig.free_disk_size,
            is_free: 1,
          });
          this.params.data_disk.push({
            // 提交的时候，根据 baseConfig.free_disk_switch 是否删除第一个数据盘
            disk_type: this.baseConfig.free_disk_type,
            size: this.baseConfig.free_disk_size,
            is_free: 1,
          });
        }
        // 默认选择cpu 内存
        // console.log('@#@#@##@#@initcpu', this.calcCpuList[0])
        // this.params.cpu = this.calcCpuList[0]?.value;
        // if (this.memoryList[0].type === "radio") {
        //   this.params.memory = this.calaMemoryList[0]?.value * 1;
        // } else {
        //   this.params.memory = this.calaMemoryList[0] * 1;
        // }
        // this.memoryName = this.calaMemoryList[0]?.value + this.baseConfig.memory_unit;
      } else {
        // 回填
        this.area_name = this.calcAreaList.filter(
          (item) => item.id === this.params.data_center_id
        )[0]?.name;
        const temp =
          this.dataList
            .filter((item) => item.id === this.country * 1)[0]
            ?.city.filter((item) => item.name === this.city)[0]?.area || [];

        this.lineList =
          temp.filter((item) => item.name === this.area_name)[0]?.line || [];
        this.lineName = this.lineList.filter(
          (item) => item.id === this.params.line_id
        )[0]?.name;
        this.cpuName = this.params.cpu + lang.mf_cores;
        this.memoryName = this.params.memory * 1 + this.baseConfig.memory_unit;
        // 处理存储
        // 系统盘
        let arr = [];
        arr.push({
          type: this.systemDiskList[0].type,
          name: lang.mf_system,
          disk_type: this.params.system_disk.disk_type,
          size: this.params.system_disk.size,
        });
        // 数据盘
        if (this.params.data_disk.length > 0) {
          this.params.data_disk.forEach((item, index) => {
            arr.push({
              min: this.dataDiskList[0].min_value,
              max: this.dataDiskList[this.dataDiskList.length - 1].max_value,
              type: this.dataDiskList[0].type,
              name:
                this.baseConfig.free_disk_switch && index === 0
                  ? lang.mf_tip37
                  : lang.common_cloud_text1,
              disk_type: item.disk_type,
              size: item.size,
              is_free: this.baseConfig.free_disk_switch && index === 0 ? 1 : 0,
            });
          });
        }
        this.storeList = arr;
      }
    },
    goBuy(id) {
      window.open(`goods.htm?id=${id}`);
    },
    getGoodsName() {
      productInfo(this.id).then((res) => {
        this.tit = res.data.data.product.name;
        document.title =
          this.commonData.website_name + "-" + res.data.data.product.name;
        if (
          res.data.data.product.customfield.product_related_limit &&
          res.data.data.product.customfield.product_related_limit.related
        ) {
          this.product_related_list =
            res.data.data.product.customfield.product_related_limit.related;
        }
      });
    },
    /* 线路 */
    changeLine(e) {
      this.lineName = e[0].name;
    },
    async getLineDetails(id) {
      try {
        if (!id) {
          return;
        }
        // 获取线路详情，
        const res = await getLineDetail({ id: this.id, line_id: id });
        this.lineDetail = res.data.data;
        if (this.lineDetail.bw) {
          if (this.isInit && this.isUpdate) {
            // 初次回填
          } else {
            this.params.bw =
              this.lineDetail.bw[0]?.value || this.lineDetail.bw[0]?.min_value;
          }

          this.bwName = this.params.bw + "M";
          // 循环生成带宽可选数组
          if (this.lineDetail.bw[0]?.type !== "radio") {
            const fArr = [];
            this.lineDetail.bw.forEach((item) => {
              fArr.push(...this.createArr([item.min_value, item.max_value]));
            });
            this.bwArr = fArr;
            this.bwTip = this.createTip(fArr);
          }
        }
        if (this.lineDetail.flow) {
          if (this.isInit && this.isUpdate) {
            // 初次回填
          } else {
            this.params.flow = this.lineDetail.flow[0]?.value;
          }
          this.flowName =
            this.params.flow > 0 ? this.params.flow + "G" : lang.mf_tip28;
        }
        this.bwMarks = this.createMarks(this.bwArr);

        // gpu
        // if (this.lineDetail.gpu) {
        //   this.gpuList = this.lineDetail.gpu;
        //   this.gpu_name = this.lineDetail.gpu_name;
        //   if (this.isInit && this.isUpdate) {
        //     // 初次回填
        //   } else {
        //     this.params.gpu_num = this.lineDetail.gpu[0]?.value;
        //   }
        // } else {
        //   this.params.gpu_num = "";
        //   this.gpu_name = "";
        // }
        // 2025/01/16 防御默认改为接口返回
        if (this.lineDetail.defence) {
          if (this.isInit && this.isUpdate) {
            // 初次回填
          } else {
            this.params.peak_defence = this.lineDetail.defence.find(
              (item) => item.value == this.lineDetail.order_default_defence
            )?.value;
          }
          this.defenseName = this.lineDetail.defence.find(
            (item) => item.value == this.params.peak_defence
          )?.desc;
        } else {
          this.defenseName = "";
          this.params.peak_defence = "";
        }
        // 处理IPV4
        if (this.lineDetail.ip) {
          if (this.isInit && this.isUpdate) {
            // 初次回填
          } else {
            this.params.ip_num =
              this.lineDetail.ip[0]?.type === "radio"
                ? this.lineDetail.ip[0]?.value
                : this.lineDetail.ip[0]?.min_value;
          }
          // 循环生成可选数组
          if (this.lineDetail.ip[0]?.type !== "radio") {
            const fArr = [];
            this.lineDetail.ip.forEach((item) => {
              fArr.push(...this.createArr([item.min_value, item.max_value]));
            });
            this.ipv4Arr = fArr;
            this.ipv4Tip = this.createTip(fArr);
          }
        } else {
          this.params.ip_num = "";
        }
        // 处理IPV6
        if (this.lineDetail.ipv6) {
          if (this.isInit && this.isUpdate) {
            // 初次回填
          } else {
            this.params.ipv6_num =
              this.lineDetail.ipv6[0]?.type === "radio"
                ? this.lineDetail.ipv6[0]?.value
                : this.lineDetail.ipv6[0]?.min_value;
          }
          // 循环生成可选数组
          if (this.lineDetail.ipv6[0]?.type !== "radio") {
            const fArr = [];
            this.lineDetail.ipv6.forEach((item) => {
              fArr.push(...this.createArr([item.min_value, item.max_value]));
            });
            this.ipv6Arr = fArr;
            this.ipv6Tip = this.createTip(fArr);
          }
        } else {
          this.params.ipv6_num = "";
        }
        // VPC不支持ipv6
        if (this.params.network_type === "vpc") {
          this.params.ipv6_num = "";
        }
        // 流量线路回填带宽
        if (this.lineDetail.bill_type === "flow") {
          this.params.bw = this.lineDetail.flow[0]?.bw[0]?.out_bw;
          this.bwName = this.params.bw + "M";
        }
        this.getCycleList();
      } catch (error) {
        console.log("####", error);
      }
    },
    changeBw(e) {
      this.params.bw = e[0].value.replace("M", "");
      // 计算价格
      this.getCycleList();
    },
    changeBwNum(num) {
      if (!this.bwArr.includes(num)) {
        this.bwArr.forEach((item, index) => {
          if (num > item && num < this.bwArr[index + 1]) {
            this.params.bw =
              num - item > this.bwArr[index + 1] - num
                ? this.bwArr[index + 1]
                : item;
          }
        });
      }
      this.getCycleList();
    },
    changeIpNum(type, num) {
      if (window.ipTimer) {
        clearTimeout(window.ipTimer);
        window.ipTimer = null;
      }
      window.ipTimer = setTimeout(() => {
        const val = type === "ipv4" ? "ip_num" : "ipv6_num";
        if (!this[`${type}Arr`].includes(num)) {
          this[`${type}Arr`].forEach((item, index) => {
            if (num > item && num < this[`${type}Arr`][index + 1]) {
              this.params[val] =
                num - item > this[`${type}Arr`][index + 1] - num
                  ? this[`${type}Arr`][index + 1]
                  : item;
            }
          });
        }
        this.getCycleList();
      }, 300);
    },
    // 选中/取消防御
    chooseDefence(e) {
      this.defenseName = e[0].desc;
      this.params.peak_defence = e[0].value;
      this.getCycleList();
    },
    // 切换流量
    changeFlow(val) {
      this.params.flow = val[0].value;
      this.getCycleList();
    },
    // 切换内存
    changeMemory(e) {
      this.params.memory = e[0].value;
      this.isChangeMemory = true;
      this.getCycleList();
    },
    createArr([m, n]) {
      // 生成数组
      let temp = [];
      for (let i = m; i <= n; i++) {
        temp.push(i);
      }
      return temp;
    },
    createTip(arr) {
      // 生成范围提示
      let tip = "";
      let num = [];
      arr.forEach((item, index) => {
        if (arr[index + 1] - item > 1) {
          num.push(index);
        }
      });
      if (num.length === 0) {
        tip = `${arr[0]}-${arr[arr.length - 1]}`;
      } else {
        tip += `${arr[0]}-${arr[num[0]]},`;
        num.forEach((item, ind) => {
          tip +=
            arr[item + 1] +
            "-" +
            (arr[num[ind + 1]] ? arr[num[ind + 1]] + "," : arr[arr.length - 1]);
        });
      }
      return tip;
    },
    createMarks(data) {
      const obj = {
        0: "",
        25: "",
        50: "",
        75: "",
        100: "",
      };
      const range = data[data.length - 1] - data[0];
      obj[0] = `${data[0]}`;
      obj[25] = `${data[0] + Math.ceil(range * 0.25)}`;
      obj[50] = `${data[0] + Math.ceil(range * 0.5)}`;
      obj[75] = `${data[0] + Math.ceil(range * 0.75)}`;
      obj[100] = `${data[data.length - 1]}`;
      return obj;
    },
    /* 网络类型 */
    changeNet() {
      if (this.params.network_type === "vpc") {
        if (this.vpcList.length === 0) {
          this.getVpcList();
        } else {
          this.params.vpc.id = this.params.vpc.id || this.vpcList[0]?.id || "";
          this.plan_way = this.plan_way || 0;
        }
      }
      /* 根据后台设置的默认 nat 开关 + vpc 等条件判断选中 */
      if (
        this.params.network_type === "vpc" ||
        (this.params.network_type === "normal" &&
          this.baseConfig.type === "lightHost")
      ) {
        if (this.baseConfig.default_nat_acl) {
          this.params.nat_acl_limit_enable = true;
        }
        if (this.baseConfig.default_nat_web) {
          this.params.nat_web_limit_enable = true;
        }
      }
      this.getCycleList();
    },
    // 获取vpc
    async getVpcList(data_id) {
      try {
        if (typeof data_id === "object") {
          data_id = "";
        }
        this.vpcLoading = true;
        const res = await getVpc({
          id: this.id,
          data_center_id: data_id || this.params.data_center_id,
          page: 1,
          limit: 1000,
        });
        this.vpcList = res.data.data.list;
        this.params.vpc.id = this.params.vpc.id || this.vpcList[0]?.id || "";
        this.plan_way = this.plan_way || 0;
        this.vpcLoading = false;
      } catch (error) {
        this.vpcLoading = false;
        showToast(error.data.msg);
      }
    },
    changeResource(val) {
      const e = val[0].name;
      this.ressourceName = e;
      this.params.resource_package_id = this.resourceList.filter(
        (item) => item.name === e
      )[0]?.id;
      this.getCycleList();
    },
    changeCpu(e) {
      // 切换cpu，改变内存
      this.isChangeArea = false;
      // 计算价格
      this.getCycleList();
    },
    changeMem(num) {
      if (!this.calaMemoryList.includes(num)) {
        this.calaMemoryList.forEach((item, index) => {
          if (num > item && num < this.calaMemoryList[index + 1]) {
            this.params.memory =
              num - item > this.calaMemoryList[index + 1] - num
                ? this.calaMemoryList[index + 1]
                : item;
          }
        });
      }
      this.isChangeMemory = true;
      this.getCycleList();
      return true;
    },
    getSelectValue(refName) {
      return this.$refs[refName].getSelectedOptions();
    },
    handelSelectImg() {
      const e = this.getSelectValue("selectPopRef");
      this.params.image_id = e[0].id;
      this.imageName = e[0].name;
      this.curImage = this.calcImageList.findIndex(
        (item) => item.id === e[0].image_group_id
      );
      this.curImageId = e[0].image_group_id;
      this.showImgPick = false;
      this.isShowImage = false;
      this.getCycleList();
    },
    // 切换套餐，自定义
    handleClick() {
      this.activeName = this.activeName === "fast" ? "custom" : "fast";
      this.params.nat_acl_limit_enable = false;
      this.params.nat_web_limit_enable = false;
      this.params.auto_renew = false;
      this.params.ip_mac_bind_enable = false;
      this.params.peak_defence = "";
      this.params.ip_num = "";
      this.params.ipv6_num = "";
      this.showImage = false;
      this.isHide = true;
      this.curImage = 0;
      this.imageName = this.version = this.imageList[0]?.image[0]?.name;
      this.curImageId = this.imageList[0]?.id;
      this.getConfig();
    },
    // 选择区域
    changeArea(val) {
      const e = val[0].name;
      this.isChangeArea = false;
      this.params.data_center_id = this.calcAreaList.filter(
        (item) => item.name === e
      )[0]?.id;
      this.lineList = this.calcAreaList.filter(
        (item) => item.name === e
      )[0]?.line;
      this.params.line_id = this.lineList[0].id;
      this.lineName = this.lineList[0].name;

      // 重新选择GPU
      this.gpuList =
        this.calcAreaList.filter((item) => item.name === e)[0]?.gpu || [];
      this.gpu_name =
        this.calcAreaList.filter((item) => item.name === e)[0]?.gpu_name || "";
      this.params.gpu_num =
        this.calcAreaList.filter((item) => item.name === e)[0]?.gpu[0]?.value ||
        "";

      // 区域变化，如果有区域限制再重置cpu, 内存 ?

      // this.params.cpu = this.cpuList[0]?.value;
      // this.cpuName = this.params.cpu + lang.mf_cores;
      // if (this.memoryList[0].type === "radio") {
      //   this.params.memory = this.calaMemoryList[0]?.value * 1;
      // } else {
      //   this.params.memory = this.calaMemoryList[0] * 1;
      // }
      // this.memoryName =
      //   this.calaMemoryList[0]?.value + this.baseConfig.memory_unit;
    },
    // 选择先线路
    chooseLine(item) {
      this.params.data_center_id = item.data_center_id;
      this.params.line_id = item.id;
    },
    // 添加数据盘
    addDataDisk() {
      // 计算默认大小，使用步长逻辑
      let defaultSize;
      if (this.dataDiskList[0].type === "radio") {
        defaultSize = this.dataDiskList[0].value;
      } else {
        const firstStepValue = this.getFirstStepValue(this.dataRangArr[this.dataType[0].value]);
        defaultSize = firstStepValue;
      }

      this.storeList.push({
        min: this.dataDiskList[0].min_value,
        max: this.dataDiskList[this.dataDiskList.length - 1].max_value,
        type: this.dataDiskList[0].type,
        name: lang.common_cloud_text1,
        disk_type: this.dataType[0].value,
        size: defaultSize,
        disk_text: this.dataType[0].label,
      });
      // 处理params
      this.params.data_disk.push({
        disk_type: this.dataType[0].value,
        size: defaultSize,
      });
      this.getCycleList();
    },
    // 编辑数据盘
    editDataDisk(index) {
      this.editDiskIndex = index;
      this.showSystemDisk = true;
    },
    // 切换数据盘类型
    changeDataDisk(val, index) {
      const e = val[0].value;
      this.storeList[index].disk_text = val[0].label;
      // 分单选和范围
      if (this.dataDiskList[0]?.type === "radio") {
        this.params.data_disk[index - 1].size = this.dataNumObj[e][0]?.value;
      } else {
        // 找到范围内第一个步长倍数的值作为初始值
        const firstStepValue = this.getFirstStepValue(this.dataRangArr[e]);
        this.params.data_disk[index - 1].size = firstStepValue;
        this.storeList[index].min = this.dataRangArr[e][0];
        this.storeList[index].max =
          this.dataRangArr[e][this.dataRangArr[e].length - 1];
      }
      this.getCycleList();
    },
    delDataDisk(index) {
      this.storeList.splice(index, 1);
      this.params.data_disk.splice(index - 1, 1);
      this.getCycleList();
    },
    // 改变系统盘数量
    changeSysNum(num) {
      const temp = this.calcSystemDiskList;

      // 如果值已经在范围内，直接使用（van-stepper已经按step处理过了）
      if (temp.includes(num)) {
        this.getCycleList();
        return;
      }

      // 否则对齐到步长
      let alignedNum = this.alignToStep(num, temp);
      if (!temp.includes(alignedNum)) {
        // 如果对齐后的值仍不在范围内，找最近的值
        temp.forEach((item, index) => {
          if (alignedNum > item && alignedNum < temp[index + 1]) {
            let res =
              alignedNum - item > temp[index + 1] - alignedNum ? temp[index + 1] : item;
            this.$nextTick(() => {
              this.params.system_disk.size = res;
            });
          }
        });
      } else {
        this.$nextTick(() => {
          this.params.system_disk.size = alignedNum;
        });
      }
      this.getCycleList();
    },
    changeDataType(item, val) {
      item.disk_type = val[0].value;
      item.disk_text = val[0].label;
    },
    changeDataNum(num, ind, isFree = 0) {
      // 数据盘数量改变计算价格
      let temp = this.dataRangArr[this.params.data_disk[ind - 1].disk_type];
      if (isFree) {
        const _temp = JSON.parse(JSON.stringify(this.freeDataRange));
        if (_temp.length > 0) {
          temp = _temp;
        }
      }

      // 如果值已经在范围内，直接使用（van-stepper已经按step处理过了）
      if (temp.includes(num)) {
        setTimeout(() => {
          this.getCycleList();
        });
        return;
      }

      // 否则对齐到步长
      let alignedNum = this.alignToStep(num, temp);
      if (!temp.includes(alignedNum)) {
        // 如果对齐后的值仍不在范围内，找最近的值
        temp.forEach((item, index) => {
          if (alignedNum > item && alignedNum < temp[index + 1]) {
            let res =
              alignedNum - item > temp[index + 1] - alignedNum ? temp[index + 1] : item;
            this.$nextTick(() => {
              this.params.data_disk[ind - 1].size = res;
            });
          }
        });
      } else {
        this.$nextTick(() => {
          this.params.data_disk[ind - 1].size = alignedNum;
        });
      }
      setTimeout(() => {
        this.getCycleList();
      });
    },
    // 初始化处理系统盘，数据盘类型
    handlerType(data, type) {
      data.forEach((item) => {
        const temp = item.other_config.disk_type;
        const num = item.value;
        len = this[`${type}Type`].filter((el) => el.value === temp);
        // 处理类型 systemType, dataType
        if (len.length === 0) {
          this[`${type}Type`].push({
            value: temp,
            label:
              item.customfield.multi_language.other_config?.disk_type ||
              item.other_config?.disk_type ||
              lang.mf_no,
          });
        }
        // 处理数量选择 dataNumObj
        if (type === "data") {
          let arr = [];
          const filterArr = data.filter(
            (item) => item.other_config.disk_type === temp
          );
          filterArr.forEach((el) => {
            arr.push({
              value: el.value,
              label: el.value,
            });
          });

          this.dataNumObj[temp] = arr;
        }
      });
      // 根据磁盘类型处理取值范围和提示信息 systemRangArr, dataRangArr
      // 根据磁盘类型处理取值范围和提示信息 systemRangTip, dataRangTip
      this[`${type}Type`].forEach((item) => {
        const temp = this[`${type}DiskList`].filter(
          (lit) => lit.other_config.disk_type === item.value
        );
        const arr = [];
        temp.forEach((i) => {
          if (i.type === "radio") {
            arr.push(i.value);
          } else {
            arr.push(...this.createArr([i.min_value, i.max_value]));
          }
        });
        this[`${type}RangArr`][item.value] = arr;
        this[`${type}RangTip`][item.value] = this.createTip(arr);
      });
    },
    // 切换安全组
    changeGroup(val) {
      const e = val[0].value;
      if (e === lang.exist_group && this.groupList.length === 0) {
        this.getGroup();
      }
      if (e === lang.create_group) {
        // 新建安全组
        // this.groupSelect.forEach((item, index) => {
        //   const dom = this.$refs[`safe${index}`][0].$el;
        //   item.disabled =
        //     dom.offsetWidth >
        //     dom.getElementsByClassName("safe-item")[0].offsetWidth + 30;
        // });
        const temp = this.groupSelect
          .filter((item) => item.check)
          .reduce((all, cur) => {
            all.push(cur.value);
            return all;
          }, []);
        this.params.security_group_protocol = temp;
      } else {
        this.params.security_group_protocol = [];
      }
      this.params.security_group_id = "";
    },
    async getGroup() {
      try {
        this.groupLoading = true;
        const res = await getGroup({
          page: 1,
          limit: 1000,
        });
        this.groupList = res.data.data.list;
        this.groupLoading = false;
      } catch (error) {
        this.groupLoading = false;
        showToast(error.data.msg);
      }
    },
    // 切换登录方式
    changeLogin(val) {
      const e = val[0].value;
      this.params.password = "";
      this.params.ssh_key_id = "";
      this.showSsh = false;
      if (e === lang.security_tab1 && this.sshList.length === 0) {
        this.getSsh();
      }
      if (e === lang.auto_create) {
        this.createPassword();
      }
    },
    async getSsh() {
      try {
        this.sshLoading = true;
        const res = await getSshList({
          page: 1,
          limit: 1000,
        });
        this.sshList = res.data.data.list;
        this.sshLoading = false;
      } catch (error) {
        this.sshLoading = false;
        showToast(error.data.msg);
      }
    },
    // 生成随机密码
    createPassword() {
      let passLen = 9;
      if (
        this.baseConfig.custom_rand_password_rule &&
        this.baseConfig.default_password_length
      ) {
        passLen = this.baseConfig.default_password_length - 3;
      }
      const password = genEnCode(passLen, 1, 1, 0, 1, 0);
      const result =
        randomCoding(1) +
        randomCoding(1).toLocaleLowerCase() +
        password +
        Math.floor(Math.random() * 10);
      this.params.password = result;
      this.changeInput(result);
    },
    changeSshPort() {
      // 端口是 22 或者 100-65535 之间的数字
      if (this.params.port < 22 || this.params.port > 65535) {
        this.params.port = 22;
      }
    },
    // 根据步长对齐数值，确保是步长的倍数
    alignToStep(value, rangeArray) {
      if (this.diskStep <= 0) return value;

      // 计算最接近的步长倍数
      const aligned = Math.round(value / this.diskStep) * this.diskStep;

      // 如果对齐后的值在范围内，直接返回
      if (rangeArray.includes(aligned)) {
        return aligned;
      }

      // 如果不在范围内，找到范围内最接近的步长倍数
      let closestValue = null;
      let minDiff = Infinity;

      // 遍历范围数组，找到最接近的步长倍数
      for (let i = 0; i < rangeArray.length; i++) {
        const rangeValue = rangeArray[i];
        // 检查这个值是否是步长的倍数
        if (rangeValue % this.diskStep === 0) {
          const diff = Math.abs(value - rangeValue);
          if (diff < minDiff) {
            minDiff = diff;
            closestValue = rangeValue;
          }
        }
      }

      return closestValue || rangeArray[0];
    },
    // 获取范围内第一个步长倍数的值
    getFirstStepValue(rangeArray) {
      if (this.diskStep <= 0) return rangeArray[0];

      for (let i = 0; i < rangeArray.length; i++) {
        if (rangeArray[i] % this.diskStep === 0) {
          return rangeArray[i];
        }
      }

      return rangeArray[0];
    },
    changeInput(val) {
      this.hasLen = val.length >= 6;
      this.hasAppoint = /[^A-Za-z\d~!@#$&*()_\-+=|{}[\];:<>?,./]/.test(val);
      this.hasMust = /(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])/.test(val);
      this.hasLine = val[0] === "/";
      if (this.hasLen && !this.hasAppoint && this.hasMust && !this.hasLine) {
        this.showPas = false;
      }
    },
    changeRepas(val) {
      if (val && val === this.params.password) {
        this.showRepass = false;
      }
    },
    // 切换套餐
    async changeRecommend() {
      this.cloudIndex = this.clickIndex;
      const item = this.recommendList[this.clickIndex];
      if (this.packageId === item.id) {
        this.isShowRecommend = false;
        return;
      }
      // 赋值
      this.packageId = item.id;
      const temp = JSON.parse(JSON.stringify(item));
      temp.system_disk = {
        size: temp.system_disk_size,
        disk_type: temp.system_disk_type,
      };
      delete temp.data_disk_size;
      delete temp.data_disk_type;
      delete temp.system_disk_size;
      delete temp.system_disk_type;
      this.gpu_name = item.gpu_name;
      Object.assign(this.params, temp);
      this.lineType = item.bill_type;
      this.params.data_disk = [];
      if (item.data_disk_size * 1) {
        this.params.data_disk.push({
          size: item.data_disk_size,
          disk_type: item.data_disk_type,
        });
      } else {
        this.params.data_disk = [];
      }
      this.params.name = "";
      if (item.ipv6_num > 0) {
        this.params.network_type = "normal";
      }
      this.params.ipv6_num = item.ipv6_num;
      const res = await getLineDetail({ id: this.id, line_id: item.line_id });
      this.lineDetail = res.data.data;
      if (this.lineDetail.defence && this.lineDetail.defence.length > 0) {
        this.params.peak_defence = this.lineDetail.defence.find(
          (item) => item.value == this.lineDetail.order_default_defence
        )?.value;
        this.defenseName = this.lineDetail.defence.find(
          (item) => item.value == this.params.peak_defence
        )?.desc;
      }
      this.getCycleList();
      this.isShowRecommend = false;
    },
    // 切换城市
    changeCity(e) {
      this.country = e[0].id;
      this.city = e[1].name;
      this.changeCountry();
      this.isChangeArea = true;
      this.cloudIndex = 0;
      this.handlerFast();
    },
    tableRowClassName({ row, rowIndex }) {
      row.index = rowIndex;
    },

    // 提交前格式化数据
    formatData() {
      const temp = this.groupSelect
        .filter((item) => item.check)
        .reduce((all, cur) => {
          all.push(cur.value);
          return all;
        }, []);
      this.params.security_group_protocol = temp;
      // if (this.params.vpc.id === 0) {
      //   this.params.vpc.id = ''
      // }
      if (this.plan_way === 0) {
        this.params.vpc.ips = "";
      }
      if (!this.params.image_id) {
        if (this.activeName === "fast") {
          document.getElementById("image") &&
            document
              .getElementById("image")
              .scrollIntoView({ behavior: "smooth" });
        } else {
          document.getElementById("image1") &&
            document.getElementById("image1").scrollIntoView({
              behavior: "smooth",
              block: "end",
              inline: "nearest",
            });
        }
        this.showImage = true;
        return;
      }
      // 自动创建密码
      if (this.login_way === lang.auto_create && !this.params.password && bol) {
        this.createPassword();
        // showToast(
        //   `${lang.placeholder_pre1}${lang.login_password}`
        // );
      }
      // 设置密码
      if (this.login_way === lang.set_pas) {
        // 一个不满足都需要提示
        if (this.isUpdate) {
          this.changeInput(this.params.password);
        }

        if (this.hasLen && !this.hasAppoint && this.hasMust && !this.hasLine) {
        } else {
          document.getElementById("ssh").scrollIntoView({ behavior: "smooth" });
          this.showPas = true;
          return;
        }
      }
      if (
        this.login_way === lang.set_pas &&
        this.params.password !== this.params.re_password
      ) {
        document.getElementById("ssh").scrollIntoView({ behavior: "smooth" });
        this.showRepass = true;
        return;
      }
      // ssh
      if (this.login_way === lang.security_tab1 && !this.params.ssh_key_id) {
        document.getElementById("ssh").scrollIntoView({ behavior: "smooth" });
        this.showSsh = true;
        return;
      }
      return true;
    },
    formatSwitch(data) {
      data.config_options.auto_renew = data.config_options.auto_renew ? 1 : 0;
      data.config_options.ip_mac_bind_enable = data.config_options
        .ip_mac_bind_enable
        ? 1
        : 0;
      data.config_options.nat_acl_limit_enable = data.config_options
        .nat_acl_limit_enable
        ? 1
        : 0;
      data.config_options.nat_web_limit_enable = data.config_options
        .nat_web_limit_enable
        ? 1
        : 0;
      data.config_options.ipv6_num_enable = data.config_options.ipv6_num_enable
        ? 1
        : 0;
      // ssh端口设置，自定义端口
      if (this.baseConfig.rand_ssh_port === 3) {
        if (this.ssh_port_type === 1) {
          data.config_options.port = null;
          data.config_options.rand_port = 1;
        } else {
          data.config_options.rand_port = 0;
          if (this.ssh_port_type === 0) { // 根据系统，win 3389 liunx 22
            if (this.imageName.indexOf("Win") !== -1) {
              data.config_options.port = 3389;
            } else {
              data.config_options.port = 22;
            }
          }
        }
      }
      // 随机密码
      if (this.login_way === lang.auto_create) {
        data.config_options.rand_password = 1;
      } else {
        data.config_options.rand_password = 0;
      }
      return data;
    },
    // 立即购买
    submitOrder(e) {
      if (e.data && e.data.type !== "iframeBuy") {
        return;
      }
      if (
        Boolean(
          (JSON.parse(localStorage.getItem("common_set_before")) || {})
            .custom_fields?.before_settle === 1
        )
      ) {
        window.open("/account.htm");
        return;
      }
      this.$refs.orderForm.validate().then(async () => {
        const bol = this.formatData();
        if (bol !== true) {
          return;
        }
        const flag = await this.$refs.customGoodRef.getSelfDefinedField();
        if (!flag) return;
        try {
          const params = {
            product_id: this.id,
            config_options: {
              ...JSON.parse(JSON.stringify(this.params)),
            },
            qty: this.qty,
            customfield: this.promo,
            self_defined_field: this.self_defined_field,
          };
          // if (
          //   this.baseConfig.free_disk_switch &&
          //   this.activeName === "custom"
          // ) {
          //   params.config_options.data_disk.shift();
          // }
          if (this.lineDetail.bill_type === "bw") {
            delete params.flow;
          } else {
            delete params.bw;
          }
          if (this.activeName === "fast") {
            params.config_options.recommend_config_id = this.packageId;
          }
          // 处理自动续费，其他配置等
          const _temp = this.formatSwitch(params);
          if (e.data && e.data.type === "iframeBuy") {
            const postObj = {
              type: "iframeBuy",
              params: _temp,
              price: this.calcTotalPrice,
            };
            window.parent.postMessage(JSON.parse(JSON.stringify(postObj)), "*");
            return;
          }
          // 直接传配置到结算页面
          sessionStorage.setItem("product_information", JSON.stringify(_temp));
          location.href = `/cart/settlement.htm?id=${params.product_id}`;
        } catch (error) {
          showToast(error.data.msg);
        }
      });
    },
    handlerCart() {
      if (this.isUpdate && !this.isConfig) {
        this.changeCart();
      } else {
        this.addCart();
      }
    },
    // 加入购物车
    addCart() {
      this.$refs.orderForm.validate().then(async (res) => {
        const bol = this.formatData();
        if (bol !== true) {
          return;
        }
        const flag = await this.$refs.customGoodRef.getSelfDefinedField();
        if (!flag) return;
        try {
          const params = {
            product_id: this.id,
            config_options: {
              ...JSON.parse(JSON.stringify(this.params)),
              // 其他需要回显的页面数据
              activeName: this.activeName,
              country: this.country,
              defenseName: this.defenseName,
              countryName: this.countryName,
              city: this.city,
              curImage: this.curImage,
              curImageId: this.curImageId,
              imageName: this.imageName,
              version: this.version,
              cloudIndex: this.cloudIndex,
              login_way: this.login_way,
              groupName: this.groupName,
            },
            qty: this.qty,
            customfield: this.promo,
            self_defined_field: this.self_defined_field,
          };
          // if (
          //   this.baseConfig.free_disk_switch &&
          //   this.activeName === "custom"
          // ) {
          //   params.config_options.data_disk.shift();
          // }
          if (this.lineDetail.bill_type === "bw") {
            delete params.flow;
          } else {
            delete params.bw;
          }
          if (this.activeName === "fast") {
            params.config_options.recommend_config_id = this.packageId;
          }
          const _temp = this.formatSwitch(params);
          const res = await addToCart(_temp);
          if (res.data.status === 200) {
            this.cartDialog = true;
          }
        } catch (error) {
          console.log(error);
          showToast(error.data.msg);
        }
      });
    },
    // 修改购物车
    changeCart() {
      this.$refs.orderForm.validate().then(async () => {
        const bol = this.formatData();
        if (bol !== true) {
          return;
        }
        const flag = await this.$refs.customGoodRef.getSelfDefinedField();
        if (!flag) return;
        try {
          const params = {
            position: this.position,
            product_id: this.id,
            config_options: {
              ...JSON.parse(JSON.stringify(this.params)),
              // 其他需要回显的页面数据
              activeName: this.activeName,
              country: this.country,
              countryName: this.countryName,
              city: this.city,
              curImage: this.curImage,
              curImageId: this.curImageId,
              defenseName: this.defenseName,
              imageName: this.imageName,
              version: this.version,
              cloudIndex: this.cloudIndex,
              login_way: this.login_way,
              groupName: this.groupName,
            },
            qty: this.qty,
            customfield: this.promo,
            self_defined_field: this.self_defined_field,
          };
          // if (
          //   this.baseConfig.free_disk_switch &&
          //   this.activeName === "custom"
          // ) {
          //   params.config_options.data_disk.shift();
          // }
          if (this.lineDetail.bill_type === "bw") {
            delete params.flow;
          } else {
            delete params.bw;
          }
          if (this.activeName === "fast") {
            params.config_options.recommend_config_id = this.packageId;
          }
          this.dataLoading = true;
          const _temp = this.formatSwitch(params);
          const res = await updateCart(_temp);
          showToast(res.data.msg);
          location.href = "/cart/shoppingCar.htm";
          this.dataLoading = false;
        } catch (error) {
          showToast(error.data.msg);
        }
      });
    },
    goToCart() {
      location.href = "/cart/shoppingCar.htm";
      this.cartDialog = false;
    },
    changeCountry() {
      this.countryName = this.dataList.filter(
        (item) => item.id === this.country * 1
      )[0]?.name;
      this.isChangeArea = true;
      this.cloudIndex = 0;
      if (this.activeName === "fast") {
        this.handlerFast();
      }
    },
    changQty() {
      this.loadingPrice = true;
      this.changeConfig();
    },
    eventChange(evetObj) {
      if (this.eventData.id !== evetObj.id) {
        this.eventData.id = evetObj.id || "";
        this.promo.event_promotion = this.eventData.id;
        if (this.params.data_center_id) {
          this.changeConfig();
        }
      }
    },
    // 使用优惠码
    getDiscount(data) {
      this.promo.promo_code = data[1];
      this.changeConfig();
    },
    removeDiscountCode() {
      this.promo.promo_code = "";
      this.discount = 0;
      this.changeConfig();
    },
    // 获取镜像
    async getIamgeList(bol = false) {
      try {
        const res = await getSystemList({
          id: this.id,
          is_market: this.imageType === 'public' ? 0 : 1
        });
        const temp = res.data.data.list;
        this.imageList = temp;
        this.marketImageCount = res.data.data?.market_image_count || 0;
        if (!this.isUpdate && temp.length > 0) {
          this.imageName = this.version = temp[0]?.image[0]?.name;
          this.curImage = 0;
          this.curImageId = temp[0]?.id;
          this.params.image_id = temp[0]?.image[0]?.id;
        }
        if (bol) {
          this.changeConfig();
        }
      } catch (error) {
        console.log(error);
      }
    },
    changeDuration() {
      this.loadingPrice = true;
      this.promo.promo_code = "";
      this.discount = 0;
      this.changeConfig();
    },
    // 获取周期
    async getCycleList() {
      try {
        // 防抖
        if (window.getNowIngTimer) {
          clearTimeout(window.getNowIngTimer);
          window.getNowIngTimer = null;
        }
        window.getNowIngTimer = setTimeout(async () => {
          this.loadingPrice = true;
          const params = JSON.parse(JSON.stringify(this.params));
          params.id = this.id;
          // 免费盘不传参数
          // if (
          //   this.baseConfig.free_disk_switch &&
          //   this.activeName === "custom"
          // ) {
          //   params.data_disk.shift();
          // }
          const hasDuration = params.duration_id;
          if (hasDuration && this.configLimitList.length === 0) {
            return this.changeConfig();
          }
          if (this.activeName === "fast") {
            params.recommend_config_id = this.packageId;
          }
          const res = await getDuration(params);
          let temp = res.data.data;
          // 根据限制处理周期
          if (this.configLimitList.length > 0) {
            if (this.activeName === "fast") {
              // 套餐
              const tempDur = this.configLimitList
                .reduce((all, cur) => {
                  if (cur.rule.recommend_config) {
                    all.push(cur.rule);
                  }
                  return all;
                }, [])
                .filter((item) =>
                  item.recommend_config.id.includes(params.recommend_config_id)
                );
              let packageArr = [],
                durationArr = [];
              tempDur.length > 0 &&
                tempDur.forEach((item) => {
                  packageArr.push(...item.recommend_config.id);
                  durationArr.push(...item.duration.id);
                });
              if (
                Array.from(new Set(packageArr)).includes(
                  params.recommend_config_id
                )
              ) {
                temp = temp.filter((item) =>
                  Array.from(new Set(durationArr)).includes(item.id)
                );
              }
              if (this.isInit && this.isUpdate) {
              } else {
                this.params.duration_id = "";
              }
            } else {
              // 自定义
            }
          }
          this.cycleList = temp;
          if (this.isInit && this.isUpdate) {
          } else {
            // this.params.duration_id =
            //   this.params.duration_id || this.cycleList[0]?.id;
            const defaultCycle = this.cycleList.filter(item =>
              item.id !== "ontrial" && (item.is_default === 1 || this.cycleList.every(x => x.is_default !== 1))
            );
            const hasNowDuration = this.cycleList.find(
              (item) => item.id == this.params.duration_id
            );
            this.params.duration_id = hasNowDuration
              ? this.params.duration_id
              : defaultCycle[0]?.id;
          }
          this.changeConfig();
        }, 200);
      } catch (error) {
        console.log("error", error);
      }
    },
    // 更改配置计算价格
    async changeConfig() {
      try {
        const params = {
          id: this.id,
          config_options: {
            ...JSON.parse(JSON.stringify(this.params)),
            promo_code: this.promo.promo_code,
            event_promotion: this.promo.event_promotion,
          },
          qty: this.qty,
        };
        // if (this.baseConfig.free_disk_switch && this.activeName === "custom") {
        //   params.config_options.data_disk.shift();
        // }
        if (this.activeName === "fast") {
          params.config_options.recommend_config_id = this.packageId;
        }
        let timer = null;
        if (!params.config_options.image_id && timer === null) {
          setTimeout(() => {
            this.params.image_id = this.calcImageList[0]?.image[0]?.id;
            this.changeConfig();
          }, 300);
          return;
        } else {
          clearTimeout(timer);
          timer = null;
        }
        this.loadingPrice = true;
        const res = await calcPrice(params);
        this.totalPrice = res.data.data.price * 1;
        this.calcTotalPrice = res.data.data.price_total * 1;
        this.eventData.discount =
          res.data.data.price_event_promotion_discount * 1 || 0;
        this.discount = res.data.data.price_promo_code_discount * 1 || 0;
        this.levelNum = res.data.data.price_client_level_discount * 1 || 0;
        this.preview = res.data.data.preview;
        if (res.data.data.sub_host.length > 0) {
          this.sonPreview = res.data.data.sub_host.map((item) => item.preview);
        } else {
          this.sonPreview = [];
        }
        this.duration = res.data.data.duration;
        this.isInit = false;
        this.loadingPrice = false;
      } catch (error) {
        console.log("error", error);
        this.loadingPrice = false;
        showToast(error.data.msg);
      }
    },
    // 获取通用配置
    getCommonData() {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"));
      document.title = this.commonData.website_name + "-" + this.tit;
    },
    mouseenter(index) {
      // if (index === this.curImage) {
      //   this.hover = true
      // }
      this.curImage = index;
      this.hover = true;
    },
    changeImage(item, index) {
      this.curImageId = item.id;
      this.showImgPick = true;
      this.getCycleList();
    },
    chooseVersion(ver, id) {
      this.curImageId = id;
      this.version = ver.name;
      this.params.image_id = ver.id;
      this.isManual = true;
      this.getCycleList();
    },
    /* vpc校验规则 */
    changeVpc3() {
      switch (this.vpc_ips.vpc6.value) {
        case 16:
          this.vpc_ips.vpc3 = 0;
          break;
        case 17:
          this.vpc_ips.vpc3 = this.near([0, 128], this.vpc_ips.vpc3);
          break;
        case 18:
          this.vpc_ips.vpc3 = this.near([0, 64, 128, 192], this.vpc_ips.vpc3);
          break;
        case 19:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(32, 224)],
            this.vpc_ips.vpc3
          );
          break;
        case 20:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(16, 240)],
            this.vpc_ips.vpc3
          );
          break;
        case 21:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(8, 248)],
            this.vpc_ips.vpc3
          );
          break;
        case 22:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(4, 252)],
            this.vpc_ips.vpc3
          );
          break;
        case 23:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(2, 254)],
            this.vpc_ips.vpc3
          );
          break;
      }
    },
    changeVpc4() {
      switch (this.vpc_ips.vpc6.value) {
        case 25:
          this.vpc_ips.vpc4 = this.near([0, 128], this.vpc_ips.vpc4);
          break;
        case 26:
          this.vpc_ips.vpc4 = this.near([0, 64, 128, 192], this.vpc_ips.vpc4);
          break;
        case 27:
          this.vpc_ips.vpc4 = this.near(
            [0, ...this.productArr(32, 224)],
            this.vpc_ips.vpc4
          );
          break;
        case 28:
          this.vpc_ips.vpc4 = this.near(
            [0, ...this.productArr(16, 240)],
            this.vpc_ips.vpc4
          );
          break;
      }
    },
    productArr(min, max, step) {
      const arr = [];
      for (let i = min; i < max + 1; i = i + min) {
        arr.push(i);
      }
      return arr;
    },
    near(arr, n) {
      arr.sort(function (a, b) {
        return Math.abs(a - n) - Math.abs(b - n);
      });
      return arr[0];
    },
    vpcFormatter(val) {
      if (val * 1 >= this.vpc_ips.max) {
        return this.vpc_ips.max;
      } else if (val * 1 <= this.vpc_ips.min) {
        return this.vpc_ips.min;
      } else {
        return val;
      }
    },
    vpc2Formatter(val) {
      if (val * 1 > 255) {
        return 255;
      } else if (val * 1 < 0) {
        return 0;
      } else {
        return val;
      }
    },
    changeVpcMask() {
      switch (this.vpc_ips.vpc6.value) {
        case 16:
          this.vpc_ips.vpc3 = 0;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc3Tips = "";
          this.vpc_ips.vpc4Tips = "";
          break;
        case 17:
          this.vpc_ips.vpc3 = this.near([0, 128], this.vpc_ips.vpc3);
          this.vpc_ips.vpc3Tips = lang.range2;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 18:
          this.vpc_ips.vpc3 = this.near([0, 64, 128, 192], this.vpc_ips.vpc3);
          this.vpc_ips.vpc3Tips = lang.range3;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 19:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(32, 224)],
            this.vpc_ips.vpc3
          );
          this.vpc_ips.vpc3Tips = lang.range4;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 20:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(16, 240)],
            this.vpc_ips.vpc3
          );
          this.vpc_ips.vpc3Tips = lang.range5;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 21:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(8, 248)],
            this.vpc_ips.vpc3
          );
          this.vpc_ips.vpc3Tips = lang.range6;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 22:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(4, 252)],
            this.vpc_ips.vpc3
          );
          this.vpc_ips.vpc3Tips = lang.range7;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 23:
          this.vpc_ips.vpc3 = this.near(
            [0, ...this.productArr(2, 254)],
            this.vpc_ips.vpc3
          );
          this.vpc_ips.vpc3Tips = lang.range8;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 24:
          this.vpc_ips.vpc3Tips = lang.range9;
          this.vpc_ips.vpc4 = 0;
          this.vpc_ips.vpc4Tips = "";
          break;
        case 25:
          this.vpc_ips.vpc4 = this.near([0, 128], this.vpc_ips.vpc4);
          this.vpc_ips.vpc4Tips = lang.range2;
          this.vpc_ips.vpc3Tips = lang.range1;
          break;
        case 26:
          this.vpc_ips.vpc4 = this.near([0, 64, 128, 192], this.vpc_ips.vpc4);
          this.vpc_ips.vpc4Tips = lang.range3;
          this.vpc_ips.vpc3Tips = lang.range1;
          break;
        case 27:
          this.vpc_ips.vpc4 = this.near(
            [0, ...this.productArr(32, 224)],
            this.vpc_ips.vpc4
          );
          this.vpc_ips.vpc4Tips = lang.range4;
          this.vpc_ips.vpc3Tips = lang.range1;
          break;
        case 28:
          this.vpc_ips.vpc4 = this.near(
            [0, ...this.productArr(16, 240)],
            this.vpc_ips.vpc4
          );
          this.vpc_ips.vpc4Tips = lang.range12;
          this.vpc_ips.vpc3Tips = lang.range1;
          break;
      }
    },
    changeVpcIp() {
      switch (this.vpc_ips.vpc1.value) {
        case 10:
          this.vpc_ips.vpc1.tips = lang.range1;
          this.vpc_ips.min = 0;
          this.vpc_ips.max = 255;
          break;
        case 172:
          this.vpc_ips.vpc1.tips = lang.range10;
          if (this.vpc_ips.vpc2 < 16 || this.vpc_ips.vpc2 > 31) {
            this.vpc_ips.vpc2 = 16;
          }
          this.vpc_ips.min = 16;
          this.vpc_ips.max = 31;
          break;
        case 192:
          this.vpc_ips.vpc1.tips = lang.range11;
          this.vpc_ips.vpc2 = 168;
          this.vpc_ips.min = 168;
          this.vpc_ips.max = 168;
          break;
      }
    },
  },
});
window.directiveInfo.forEach((item) => {
  app2.directive(item.name, item.fn);
});
app2.use(vant).mount("#template2");

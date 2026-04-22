const { showToast } = vant;
window.lang = Object.assign(window.lang, window.module_lang);
const app2 = Vue.createApp({
  components: {
    topMenu,
    curSelect,
    discountCode,
    eventCode,
    customGoods,
  },
  data() {
    return {
      lang: window.lang,
      id: "",
      position: "",
      goodsInfo: {},
      addons_js_arr: [], // 插件数组
      isShowPromo: false, // 是否开启优惠码
      isShowLevel: false, // 是否开启等级优惠
      isUseDiscountCode: false, // 是否使用优惠码
      backfill: {}, // 回填参数
      customfield: {
        event_promotion: "",
        promo_code: "",
      }, // 自定义字段
      submitLoading: false,
      commonData: {},
      self_defined_field: {},
      eventData: {
        id: "",
        discount: 0,
      },
      // 订单数据
      orderData: {
        qty: 1,
        // 是否勾选阅读
        isRead: false,
        // 付款周期
        duration: "",
      },
      // 右侧展示区域
      showInfo: [],
      activeNames: [],
      base_price: "",
      eventDiscount: 0,
      showImgPick: false,
      countryName: "",
      city: "",
      // 商品原单价
      onePrice: 0,
      // 商品原总价
      original_price: 0,
      showConfigPage: false,
      timerId: null, // 订单id
      basicInfo: {}, // 基础信息
      configoptions: [], // 配置项
      custom_cycles: [], // 自定义周期
      selectDuration: {},
      isShowDur: false,
      curCycle: 0,
      totalPrice: 0,
      cycle: "",
      calcImageList: [],
      onetime: "",
      pay_type: "",
      // 提交数据
      configForm: {
        // 自定义配置项
      },
      isShowBtn: false,
      // 国家列表
      countryList: [],
      // 处理过后的国家列表
      filterCountry: {},
      curCountry: {}, // 当前国家，根据配置id存入对应的初始索引
      cartDialog: false,
      dataLoading: false,
      // 客户等级折扣金额
      clDiscount: 0,
      // 优惠码折扣金额
      code_discount: 0,
      hasScroll: false,
      hasTopScroll: false,
      isShowImage: false,
      imageName: "",
      osIcon: "",
      areImg: "",
      calcOsImgList: [],
      dataCenterList: [],
      curOsItem: {},
      handNum: 0,
      firstDueTime: 0,
      cascadeStates: {},
      cascadeTimers: {}
    };
  },
  created() {
    if (window.performance.navigation.type === 2) {
      sessionStorage.removeItem("product_information");
    }
    this.id = location.href.split("?")[1].split("=")[1]?.split("&")[0];
    this.getCommonData();

    // 回显配置
    //const temp = this.getQuery(location.search)

    const temp = JSON.parse(sessionStorage.getItem("product_information"));
    if (temp && temp.config_options) {
      this.backfill = temp.config_options;
      this.configForm.config_options = temp.config_options;
      this.customfield = temp.customfield;
      this.self_defined_field = temp.self_defined_field || {};
      this.cycle = temp.config_options.cycle;
      this.orderData.qty = temp.qty;
      this.position = temp.position;
    }
    this.getCountryList();
  },
  mounted() {
    this.addons_js_arr = JSON.parse(
      document.querySelector("#addons_js").getAttribute("addons_js")
    ); // 插件列表
    const arr = this.addons_js_arr.map((item) => {
      return item.name;
    });
    if (arr.includes("PromoCode")) {
      // 开启了优惠码插件
      this.isShowPromo = true;
    }
    if (arr.includes("IdcsmartClientLevel")) {
      // 开启了等级优惠
      this.isShowLevel = true;
    }
    this.getConfig();
    window.addEventListener("message", (event) => this.buyNow(event));
  },
  updated() { },
  destroyed() { },
  computed: {
    calStr() {
      const temp = this.basicInfo?.order_page_description
        ?.replace(/&lt;/g, "<")
        .replace(/&gt;/g, ">")
        .replace(/&quot;/g, '"')
        .replace(/&/g, "&")
        .replace(/"/g, '"')
        .replace(/'/g, "'");
      return temp;
    },
    calcDes() {
      return (val) => {
        const temp = val
          .replace(/&lt;/g, "<")
          .replace(/&gt;/g, ">")
          .replace(/&quot;/g, '"')
          .replace(/&/g, "&")
          .replace(/"/g, '"')
          .replace(/'/g, "'");
        return temp;
      };
    },
    calcSwitch() {
      return (item, type) => {
        if (type) {
          return item.subs[0]?.id;
        } else {
          return item.subs[1]?.id;
        }
      };
    },
    calcCountry() {
      return (val) => {
        return this.countryList.filter((item) => val === item.iso)[0]?.name_zh;
      };
    },
    calcCity() {
      return (id) => {
        return this.filterCountry[id].filter(
          (item) => item[0]?.country === this.curCountry[id]
        )[0];
      };
    },
  },

  methods: {
     // #region ============== 级联 ==============
    // 回填方法
    /**
     * 回填级联选中状态
     * @param {Object} savedData
     */
    restoreCascadeSelections(savedData) {
      if (!savedData || typeof savedData !== 'object') return;
      this.configoptions.forEach(item => {
        if (item.option_type !== 'cascade') return;
        if (!savedData[item.id]) return;
        const { item_id, quantity } = savedData[item.id];
        const targetItemId = Number(item_id);
        // 从树中找到目标叶子节点的完整路径
        const path = this.findPathInTree(item.tree, targetItemId);
        if (!path || path.length === 0) return;
        // 回填选中状态
        const ids = path.map(node => node.item_id);
        const items = path.map(node => node);
        this.cascadeStates[item.id] = {
          selectedIds: ids,
          selectedItems: items,
          quantity: quantity || 1
        };
      });
    },

    /**
     * 在树中递归查找目标 item_id 的完整路径
     * @param {Array} tree - 树数据
     * @param {Number} targetItemId - 目标叶子节点的 item_id
     * @returns {Array|null} - 从根到叶子的节点数组，找不到返回 null
     */
    findPathInTree(tree, targetItemId) {
      if (!tree || tree.length === 0) return null;
      for (const node of tree) {
        // 当前节点就是目标
        if (node.item_id === targetItemId) {
          return [node];
        }
        // 递归查找子节点
        if (node.children && node.children.length > 0) {
          const childPath = this.findPathInTree(node.children, targetItemId);
          if (childPath) {
            // 找到了，把当前节点加到路径前面
            return [node, ...childPath];
          }
        }
      }
      return null;
    },
    // 初始化所有 cascade 类型的选项
    initAllCascade() {
      this.configoptions.forEach(item => {
        if (item.option_type === 'cascade' && item.tree && item.tree.length > 0) {
          this.initCascadeState(item.id, item.tree);
        }
      });
    },
    // 初始化单个 cascade 的状态：默认每层选中第一项
    initCascadeState(optionId, tree) {
      const ids = [];
      const items = [];
      let currentLevel = tree;
      while (currentLevel && currentLevel.length > 0) {
        const firstItem = currentLevel[0];
        ids.push(firstItem.item_id);
        items.push(firstItem);
        if (firstItem.is_leaf === 1) break;
        currentLevel = firstItem.children || [];
      }
      // 计算默认数量
      const lastItem = items[items.length - 1];
      let quantity = 1;
      if (lastItem && lastItem.is_leaf === 1 && lastItem.fee_type !== 'fixed') {
        const ranges = this.extractValidRanges(lastItem);
        if (ranges.length > 0) {
          quantity = ranges[0].min || 1;
        }
      }

      this.cascadeStates[optionId] = {
        selectedIds: ids,
        selectedItems: items,
        quantity: quantity
      };
    },
    // ==================== 获取状态 ====================
    getCascadeSelectedIds(optionId) {
      const state = this.cascadeStates[optionId];
      return state ? state.selectedIds : [];
    },
    getCascadeSelectedItems(optionId) {
      const state = this.cascadeStates[optionId];
      return state ? state.selectedItems : [];
    },
    // ==================== 计算展示层级 ====================
    getCascadeDisplayLevels(optionId) {
      const configItem = this.configoptions.find(c => c.id === optionId);
      if (!configItem || !configItem.tree || configItem.tree.length === 0) return [];
      const selectedIds = this.getCascadeSelectedIds(optionId);
      const levels = [];
      levels.push(configItem.tree);
      let currentItems = configItem.tree;
      for (let i = 0; i < selectedIds.length; i++) {
        const selectedId = selectedIds[i];
        const selectedItem = currentItems.find(item => item.item_id === selectedId);
        if (selectedItem && selectedItem.children && selectedItem.children.length > 0) {
          levels.push(selectedItem.children);
          currentItems = selectedItem.children;
        } else {
          break;
        }
      }
      return levels;
    },

    // ==================== 叶子节点相关 ====================
    getCascadeLeafNode(optionId) {
      const items = this.getCascadeSelectedItems(optionId);
      if (items.length === 0) return null;
      const last = items[items.length - 1];
      return last && last.is_leaf === 1 ? last : null;
    },
    getCascadeLeafFeeType(optionId) {
      const leaf = this.getCascadeLeafNode(optionId);
      if (!leaf) return 'fixed';
      return leaf.fee_type || 'fixed';
    },
    // ==================== 区间相关 ====================
    // 从叶子节点提取合法区间
    extractValidRanges(leafNode) {
      if (!leafNode || !leafNode.price || leafNode.price.length === 0) return [];
      return leafNode.price
        .map(p => ({
          min: p.qty_min,
          max: p.qty_max,
          priceItem: p
        }))
        .filter(r => r.min > 0 || r.max > 0)
        .sort((a, b) => a.min - b.min);
    },

    getCascadeValidRanges(optionId) {
      const leaf = this.getCascadeLeafNode(optionId);
      return this.extractValidRanges(leaf);
    },

    getCascadeTotalMin(optionId) {
      const ranges = this.getCascadeValidRanges(optionId);
      if (ranges.length === 0) return 1;
      return ranges[0].min || 1;
    },

    getCascadeTotalMax(optionId) {
      const ranges = this.getCascadeValidRanges(optionId);
      if (ranges.length === 0) return 999;
      return Math.max(...ranges.map(r => r.max));
    },

    isInRange(val, range) {
      return val >= range.min && val <= range.max;
    },

    isInAnyRange(val, ranges) {
      return ranges.some(r => this.isInRange(val, r));
    },

    // 修正到最近的合法值
    snapToNearestValid(val, ranges) {
      if (ranges.length === 0) return 1;
      if (this.isInAnyRange(val, ranges)) return val;
      let closest = ranges[0].min;
      let minDist = Math.abs(val - closest);
      for (const range of ranges) {
        const distMin = Math.abs(val - range.min);
        const distMax = Math.abs(val - range.max);
        if (distMin < minDist) {
          minDist = distMin;
          closest = range.min;
        }
        if (distMax < minDist) {
          minDist = distMax;
          closest = range.max;
        }
      }
      return closest;
    },
    // ==================== 选中操作 ====================
    handleCascadeSelect(optionId, levelIndex, item) {
      const state = this.cascadeStates[optionId];
      if (!state) return;
      const newIds = state.selectedIds.slice(0, levelIndex);
      const newItems = state.selectedItems.slice(0, levelIndex);
      newIds.push(item.item_id);
      newItems.push(item);
      // 自动递归选中后续每层第一项直到叶子
      let current = item;
      while (current.is_leaf === 0 && current.children && current.children.length > 0) {
        const firstChild = current.children[0];
        newIds.push(firstChild.item_id);
        newItems.push(firstChild);
        current = firstChild;
      }
      // 更新状态
      state.selectedIds = newIds;
      state.selectedItems = newItems;
      // 重置数量
      this.$nextTick(() => {
        this.updateCascadeQuantity(optionId);
        this.onCascadeChange(optionId);
      });
    },

    // 切换后重置数量
    updateCascadeQuantity(optionId) {
      if (!this.cascadeStates[optionId]) return;

      const leaf = this.getCascadeLeafNode(optionId);
      if (!leaf) return;

      if (leaf.fee_type === 'fixed') {
        this.cascadeStates[optionId].quantity = 1;
      } else {
        const ranges = this.extractValidRanges(leaf);
        if (ranges.length > 0) {
          this.cascadeStates[optionId].quantity = ranges[0].min || 1;
        } else {
          this.cascadeStates[optionId].quantity = 1;
        }
      }
    },

    // 级联数量拖动
    onCascadeQuantityChange(optionId, val) {
      if (val === undefined || val === null) return;
      const ranges = this.getCascadeValidRanges(optionId);
      if (ranges.length > 0 && !this.isInAnyRange(val, ranges)) {
        const corrected = this.snapToNearestValid(val, ranges);
        this.cascadeStates[optionId].quantity = corrected;
      }
      this.debounceCascadeChange(optionId);
    },
    // 防抖
    debounceCascadeChange(optionId) {
      if (this.cascadeTimers[optionId]) {
        clearTimeout(this.cascadeTimers[optionId]);
      }
      this.cascadeTimers[optionId] = setTimeout(() => {
        this.onCascadeChange(optionId);
      }, 500);
    },

    onCascadeChange(optionId) {
      this.changeConfig();
    },

    // ==================== 获取最终结果 ====================
    getCascadeResult(optionId) {
      const leaf = this.getCascadeLeafNode(optionId);
      if (!leaf) return {};
      const state = this.cascadeStates[optionId];
      let qty = 1;
      if (leaf.fee_type === 'fixed') {
        qty = 1;
      } else {
        qty = state ? state.quantity : 1;
      }
      return {
        [optionId]: {
          item_id: String(leaf.item_id),
          quantity: qty
        }
      };
    },

    // 获取所有 cascade 类型的配置结果（用于配置里的 cascade_configoption ）
    getAllCascadeResults() {
      const results = {};
      this.configoptions.forEach(item => {
        if (item.option_type === 'cascade') {
          const r = this.getCascadeResult(item.id);
          Object.assign(results, r);
        }
      });
      return results;
    },
    // #endregion ============== 级联 ==============
    
    multilSelectText(item) {
      const select_id = this.configForm[item.id]
      return item.subs.filter((sub) => select_id.includes(sub.id)).map((sub) => sub.option_name).join(",");
    },
    // 返回产品列表页
    goBack() {
      window.history.back();
    },
    calcDataCenter(arr) {
      return arr.map((item) => {
        const obj = {
          option_name: "",
          id: "",
          city: [],
        };
        item.forEach((city) => {
          obj.option_name = city.country;
          obj.id = city.country;
          obj.country = city.country;
          obj.city.push(city);
        });
        return obj;
      });
    },
    filterMoney(money) {
      if (isNaN(money)) {
        return "0.00";
      } else {
        const temp = `${money}`.split(".");
        return parseInt(temp[0]).toLocaleString() + "." + (temp[1] || "00");
      }
    },
    formateTime(time) {
      if (time && time !== 0) {
        return formateDate(time * 1000);
      } else {
        return "--";
      }
    },

    // 解析url
    getQuery(url) {
      const str = url.substr(url.indexOf("?") + 1);
      const arr = str.split("&");
      const res = {};
      for (let i = 0; i < arr.length; i++) {
        const item = arr[i].split("=");
        res[item[0]] = item[1];
      }
      return res;
    },
    async getCountryList() {
      try {
        const res = await getCountry();
        this.countryList = res.data.data.list;
      } catch (error) { }
    },
    createArr([m, n], step) {
      // 每个阶段最小值开始计算，最小值可取
      let temp = [m];
      let cur = m;
      while (cur < n && cur + step <= n) {
        cur += step;
        temp.push(cur);
      }
      return temp;
    },
    async getConfig() {
      try {
        const res = await getCommonDetail(this.id);
        const temp = res.data.data;
        this.basicInfo = temp.common_product;
        const result = temp.configoptions
          .filter((item) => item.subs.length)
          .map((item) => {
            if (item.option_type === "quantity_range") {
              item.rangeOption = [];

              item.subs = item.subs.sort((a, b) => a.qty_min - b.qty_min);
              item.subs.forEach((sub) => {
                item.rangeOption.push(
                  ...this.createArr([sub.qty_min, sub.qty_max], item.qty_change)
                );
              });
            }
            return item;
          });
        this.configoptions = result;
        this.initAllCascade();
        this.custom_cycles = temp.custom_cycles;
        this.pay_type = temp.common_product.pay_type;
        this.onetime =
          temp.cycles.onetime === "-1.00" ? "0.00" : temp.cycles.onetime;
        // 初始化自定义配置参数
        const obj = this.configoptions.reduce((all, cur) => {
          all[cur.id] =
            cur.option_type === "multi_select" ||
              cur.option_type === "quantity" ||
              cur.option_type === "quantity_range"
              ? [
                cur.option_type === "multi_select"
                  ? cur.subs[0].id
                  : cur.subs[0].qty_min,
              ]
              : cur.subs[0].id;
          // 区域的时候保存国家
          if (cur.option_type === "area") {
            this.filterCountry[cur.id] = this.toTree(cur.subs);
            this.dataCenterList = this.calcDataCenter(
              this.filterCountry[cur.id]
            );
            this.countryName = this.calcCountry(
              this.dataCenterList[0].option_name
            );
            this.city = this.dataCenterList[0].city[0].option_name;
            this.areImg = this.dataCenterList[0].option_name;
            this.curCountry[cur.id] = 0;
          }

          if (cur.option_type === "os") {
            all[cur.id] = cur.subs[0].version[0].id;
            this.osIcon =
              "/plugins/reserver/idcsmart_common/template/clientarea/mobile/mfm201/img/idcsmart_common/" +
              cur.subs[0].os.toLowerCase() +
              ".svg";
            this.imageName = cur.subs[0].version[0].option_name;
          }
          return all;
        }, {});
        this.configForm = obj;
        if (this.pay_type === "onetime") {
          this.cycle = "onetime";
        } else if (this.pay_type === "free") {
          this.cycle = "free";
        } else {
          this.cycle = temp.custom_cycles[0].id;
          this.selectDuration = temp.custom_cycles[0];
        }
        this.restoreCascadeSelections(this.backfill.cascade_configoption);
        // this.changeConfig()
        this.changeConfig(this.backfill.cycle ? true : false);
      } catch (error) { }
    },

    // 数组转树
    toTree(data) {
      const temp = Object.values(
        data.reduce((res, item) => {
          res[item.country]
            ? res[item.country].push(item)
            : (res[item.country] = [item]);
          return res;
        }, {})
      );
      return temp;
    },
    multiSelecttem(e, item) {
      this.changeConfig();
    },
    // 使用优惠码
    getDiscount(data) {
      this.customfield.promo_code = data[1];
      this.isUseDiscountCode = true;
      this.changeConfig();
    },
    sliderChange(num, item) {
      if (window.timer1) {
        clearTimeout(window.timer1);
        window.timer1 = null;
      }
      window.timer1 = setTimeout(() => {
        try {
          if (!item.rangeOption.includes(num)) {
            const val = item.rangeOption.reduce((prev, curr) =>
              Math.abs(curr - num) < Math.abs(prev - num) ? curr : prev
            );
            this.configForm[item.id] = [val * 1];
          } else {
            this.configForm[item.id] = [num * 1];
          }
          this.handNum++;
          this.changeConfig();
        } catch (error) {
          console.log("error", error);
        }
      }, 800);
    },
    eventChange(evetObj) {
      if (this.eventData.id !== evetObj.id) {
        this.eventData.id = evetObj.id || "";
        this.customfield.event_promotion = this.eventData.id;
        this.changeConfig();
      }
    },
    // 更改配置计算价格
    async changeConfig(bol = false) {
      try {
        if (bol === true) {
          /* 处理 quantity quantity_range  */
          const _temp = this.backfill.configoption;
          Object.keys(_temp).forEach((item) => {
            const type = this.configoptions.filter((el) => el.id === item)[0]
              ?.option_type;
            if (type === "quantity" || type === "quantity_range") {
              _temp[item] = _temp[item][0];
            }
          });
          this.configForm = _temp;
          this.cycle = this.backfill.cycle;
          this.curCycle = this.custom_cycles.findIndex(
            (item) => item.id * 1 === this.cycle * 1
          );
        }
        const temp = this.formatData();

        const params = {
          id: this.id,
          config_options: {
            configoption: temp,
            cascade_configoption: this.getAllCascadeResults(),
            cycle: this.cycle,
            promo_code: this.customfield.promo_code,
            event_promotion: this.customfield.event_promotion,
          },
          qty: this.orderData.qty,
        };
        this.dataLoading = true;
        const res = await calcPrice(params);

        this.original_price = res.data.data.price * 1;
        this.totalPrice = res.data.data.price_total * 1;
        this.clDiscount = res.data.data.price_client_level_discount * 1 || 0;
        this.code_discount = res.data.data.price_promo_code_discount * 1 || 0;
        this.eventData.discount =
          res.data.data.price_event_promotion_discount * 1 || 0;

        this.base_price = res.data.data.base_price;
        this.showInfo = res.data.data.preview;
        this.firstDueTime = res.data.data.due_time || 0;
        this.onePrice = res.data.data.price; // 原单价
        this.orderData.duration = res.data.data.duration;

        // 重新计算周期显示
        const result = await calculate(params);
        this.custom_cycles = result.data.data.custom_cycles;
        this.onetime = result.data.data.cycles.onetime;
        this.dataLoading = false;
      } catch (error) {
        this.dataLoading = false;
      }
    },
    removeDiscountCode() {
      this.isUseDiscountCode = false;
      this.customfield.promo_code = "";
      this.code_discount = 0;
      this.changeConfig();
    },
    // 切换国家
    changeCountry(id, index) {
      this.curCountry[id] = index;
      this.configForm[id] = this.filterCountry[id][index][0]?.id;
      this.changeConfig();
    },
    // 切换城市
    changeCity(e, item) {
      this.countryName = this.calcCountry(e[0].option_name);
      this.city = e[1].option_name;
      this.configForm[item.id] = e[1].id;
      this.changeConfig();
    },
    // 切换单击选择
    changeClick() {
      this.changeConfig();
    },
    // 切换数量
    changeNum(val, id) {
      this.configForm[id] = [val * 1];
      this.changeConfig();
    },
    // 切换周期
    changeCycle(item, index) {
      if (item.id === this.cycle) {
        return;
      }
      this.selectDuration = item;
      this.cycle = item.id;
      this.curCycle = index;
      this.changeConfig();
    },
    // 商品购买数量减少
    delQty() {
      if (this.basicInfo.allow_qty === 0) {
        return false;
      }
      if (this.orderData.qty > 1) {
        this.orderData.qty--;
        this.changeConfig();
      }
    },
    // 商品购买数量增加
    addQty() {
      if (this.basicInfo.allow_qty === 0) {
        return false;
      }
      this.orderData.qty++;
      this.changeConfig();
    },
    changQty() {
      this.changeConfig();
    },

    formatData() {
      // 处理数量类型的转为数组
      const temp = JSON.parse(JSON.stringify(this.configForm));
      Object.keys(temp).forEach((el) => {
        const arr = this.configoptions.filter((item) => item.id * 1 === el * 1);
        if (
          arr[0].option_type === "quantity" ||
          arr[0].option_type === "quantity_range" ||
          arr[0].option_type === "multi_select"
        ) {
          if (typeof temp[el] !== "object") {
            temp[el] = [temp[el]];
          }
        }
      });
      return temp;
    },
    clickOsItem(item) {
      this.curOsItem = item;
      this.calcImageList = item.subs;
      this.isShowImage = true;
    },
    changeImage(item) {
      this.calcOsImgList = item.version.map((version) => {
        return {
          ...version,
          os: item.os,
        };
      });
      this.showImgPick = true;
    },
    getSelectValue(refName) {
      return this.$refs[refName].getSelectedOptions();
    },
    handelSelectImg() {
      const e = this.getSelectValue("selectPopRef");
      this.osIcon =
        "/plugins/reserver/idcsmart_common/template/clientarea/mobile/mfm201/img/idcsmart_common/" +
        e[0].os.toLowerCase() +
        ".svg";
      this.configForm[this.curOsItem.id] = e[0].id;
      this.imageName = e[0].option_name;
      this.showImgPick = false;
      this.isShowImage = false;
      this.changeConfig();
    },
    // 立即购买
    async buyNow(e) {
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
      const flag = await this.$refs.customGoodRef.getSelfDefinedField();
      if (!flag) return;

      const temp = this.formatData();
      const params = {
        product_id: this.id,
        config_options: {
          configoption: temp,
          cascade_configoption: this.getAllCascadeResults(),
          cycle: this.cycle,
        },
        qty: this.orderData.qty,
        customfield: this.customfield,
        self_defined_field: this.self_defined_field,
      };
      const enStr = encodeURI(JSON.stringify(params.config_options));
      console.log("enStr:", enStr);
      console.log("deStr:", decodeURI(enStr));
      // 直接传配置到结算页面
      if (e.data && e.data.type === "iframeBuy") {
        const postObj = { type: "iframeBuy", params, price: this.totalPrice };
        window.parent.postMessage(JSON.parse(JSON.stringify(postObj)), "*");
        return;
      }
      window.parent.location.href = `/cart/settlement.htm?id=${params.product_id}`;
      sessionStorage.setItem("product_information", JSON.stringify(params));
    },
    // 加入购物车
    async addCart() {
      const flag = await this.$refs.customGoodRef.getSelfDefinedField();
      if (!flag) return;
      try {
        const temp = this.formatData();
        const params = {
          product_id: this.id,
          config_options: {
            configoption: temp,
            cascade_configoption: this.getAllCascadeResults(),
            cycle: this.cycle,
          },
          qty: this.orderData.qty,
          customfield: this.customfield,
          self_defined_field: this.self_defined_field,
        };
        const res = await addToCart(params);
        if (res.data.status === 200) {
          this.cartDialog = true;
          const result = await getCart();
          localStorage.setItem(
            "cartNum",
            "cartNum-" + result.data.data.list.length
          );
        }
      } catch (error) {
        showToast(error.data.msg);
      }
    },
    // 修改购物车
    async changeCart() {
      try {
        const flag = await this.$refs.customGoodRef.getSelfDefinedField();
        if (!flag) return;
        const temp = this.formatData();
        const params = {
          position: this.position,
          product_id: this.id,
          config_options: {
            configoption: temp,
            cascade_configoption: this.getAllCascadeResults(),
            cycle: this.cycle,
          },
          qty: this.orderData.qty,
          customfield: this.customfield,
          self_defined_field: this.self_defined_field,
        };
        this.dataLoading = true;
        const res = await updateCart(params);
        showToast(res.data.msg);
        setTimeout(() => {
          window.parent.location.href = `/cart/shoppingCar.htm`;
        }, 300);
        this.dataLoading = false;
      } catch (error) {
        console.log("errore", error);
        showToast(error.data.msg);
      }
    },
    goToCart() {
      window.parent.location.href = `/cart/shoppingCar.htm`;
      this.cartDialog = false;
    },
    // 支付成功回调
    paySuccess(e) {
      this.submitLoading = false;
      window.parent.location.href = "common_product_list.htm";
    },
    // 取消支付回调
    payCancel(e) {
      this.submitLoading = false;
      window.parent.location.href = "finance.htm";
    },
    // 获取通用配置
    getCommonData() {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"));
      document.title =
        this.commonData.website_name + "-" + lang.common_cloud_text109;
    },
  },
});
window.directiveInfo.forEach((item) => {
  app2.directive(item.name, item.fn);
});
app2.use(vant).mount("#template2");

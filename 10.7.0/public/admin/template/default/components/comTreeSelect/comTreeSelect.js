/* 通用treeSelect：用于商品筛选（单/多选） */
// 共享数据缓存，避免多个组件实例重复请求
const sharedDataCache = {
  proList: null,
  firstGroup: null,
  secondGroup: null,
  loading: false,
  loadingPromise: null,
};

const comTreeSelect = {
  template: `
      <t-tree-select
        :data="proData"
        v-model="checkPro"
        :popup-props="popupProps"
        :tree-props="treeProps"
        filterable
        clearable
        :multiple="multiple"
        :min-collapsed-num="1"
        :placeholder="prePlaceholder"
        :disabled="disabled"
        :max="max"
        :auto-width="autowidth"
        @change="onChange">
        <template #panelTopContent>
          <t-checkbox v-model="checkAll" @change="chooseAll" class="tree-check-all" v-if="showAll">{{lang.check_all}}</t-checkbox>
        </template>
      </t-tree-select>

      `,
  data () {
    return {
      popupProps: {
        overlayInnerStyle: (trigger) => ({
          width: this.autowidth ? "" : `${trigger.offsetWidth}px`,
        }),
        overlayInnerClassName: "com-tree-select",
      },
      proData: [],
      checkPro: "",
      isInit: true,
      checkAll: false,
      proList: [],
      secondGroup: [],
      firstGroup: [],
    };
  },
  props: {
    treeProps: {
      default () {
        return {
          valueMode: "onlyLeaf",
          label: (h, { data }) =>
            h(
              "span",
              { style: data.hidden === 1 ? "color: red" : "" },
              data.name
            ),
          keys: {
            label: "name",
            value: "key",
            children: "children",
          },
        };
      },
    },
    autowidth: {
      default () {
        return false;
      },
    },
    // 是否多选
    multiple: {
      default () {
        return false;
      },
    },
    showAll: {
      // 是否展示全选
      default () {
        return false;
      },
    },
    disabled: {
      // 是否禁用
      default () {
        return false;
      },
    },
    value: {
      // 回显传参
      default () {
        return false;
      },
    },
    max: {
      // 控制多选数量
      default () {
        return 0;
      },
    },

    prePlaceholder: {
      default () {
        return lang.product_id_empty_tip;
      },
    },
    need: {
      // 是否返回商品列表
      default () {
        return false;
      },
    },
    allProducts: {
      default () {
        return [];
      },
    },
    product: {
      default () {
        return [];
      },
    },
    // 需要再商品列表剔除的商品id
    disabledProList: {
      default () {
        return [];
      },
    },
    // 是否只显示分组
    isOnlyGroup: {
      default () {
        return false;
      },
    },
    excludeDomain: {
      //是否排除域名
      default () {
        return 0;
      },
    },
    agent: {
      default () {
        return false;
      },
    },
  },
  watch: {
    value: {
      deep: true,
      immediate: true,
      handler (val) {
        if (!val) {
          this.checkPro = this.multiple ? [] : "";
          return;
        }

        if (Array.isArray(val)) {
          this.checkPro = val.map((el) =>
            this.isOnlyGroup ? `s-${el}` : `t-${el}`
          );
        } else {
          this.checkPro = this.isOnlyGroup ? `s-${val}` : `t-${val}`;
        }
      },
    },
    isCheckAll (val) {
      this.checkAll = val;
    },
  },
  created () {
    if (this.allProducts.length > 0) {
      // 单页面多次引用组件
      this.proData = this.allProducts;
      this.proList = this.product;
      this.initDisabledPro();
      return;
    }
    // 如果有缓存数据，直接使用
    if (sharedDataCache.proList && sharedDataCache.firstGroup && sharedDataCache.secondGroup) {
      this.proList = sharedDataCache.proList;
      this.firstGroup = sharedDataCache.firstGroup;
      this.secondGroup = sharedDataCache.secondGroup;
      this.buildProData();
      return;
    }
    // 如果正在加载，等待加载完成
    if (sharedDataCache.loading && sharedDataCache.loadingPromise) {
      sharedDataCache.loadingPromise.then(() => {
        this.proList = sharedDataCache.proList;
        this.firstGroup = sharedDataCache.firstGroup;
        this.secondGroup = sharedDataCache.secondGroup;
        this.buildProData();
      });
      return;
    }
    this.init();
  },
  computed: {
    isCheckAll () {
      return (
        this.showAll &&
        this.checkPro.length ===
        (this.isOnlyGroup ? this.secondGroup.length : this.proList.length)
      );
    },
  },
  methods: {
    chooseAll (e) {
      let arr1 = [];
      if (e) {
        const originList = this.isOnlyGroup ? this.secondGroup : this.proList;
        const arr = originList.map((item) => {
          return this.isOnlyGroup ? `s-${item.id}` : `t-${item.id}`;
        });
        arr1 = originList.map((item) => item.id);
        this.checkPro = arr;
      } else {
        this.checkPro = [];
      }
      if (this.need) {
        this.$emit("choosepro", arr1, this.proList || []);
      } else {
        this.$emit("choosepro", arr1);
      }
    },
    onChange (e) {
      let val = "";
      this.isInit = false;
      if (e instanceof Object) {
        val = this.isOnlyGroup
          ? e.map((item) => Number(String(item).replace("s-", "")))
          : e.map((item) => Number(String(item).replace("t-", "")));
      } else {
        if (e) {
          val = this.isOnlyGroup
            ? Number(String(e).replace("s-", ""))
            : Number(String(e).replace("t-", ""));
        } else {
          val = "";
        }
      }
      if (this.need) {
        this.$emit("choosepro", val, this.proList || []);
      } else {
        this.$emit("choosepro", val);
      }
    },
    // 商品列表
    async getProList () {
      try {
        const res = await getComProduct({
          exclude_domain: this.excludeDomain,
        });
        const temp = res.data.data.list.map((item) => {
          item.key = `t-${item.id}`;
          return item;
        });
        // 过滤没有父级id的商品
        const list = temp.filter((item) => item.product_group_id_second);
        // 过滤是否为代理商品
        this.proList = this.agent
          ? list.filter((item) => item.agent === 1)
          : list;
        return this.proList;
      } catch (error) { }
    },
    // 获取一级分组
    async getFirPro () {
      try {
        const res = await getFirstGroup();
        this.firstGroup = res.data.data.list.map((item) => {
          item.key = `f-${item.id}`;
          return item;
        });
        return this.firstGroup;
      } catch (error) { }
    },
    // 获取二级分组
    async getSecPro () {
      try {
        const res = await getSecondGroup();
        this.secondGroup = res.data.data.list.map((item) => {
          item.key = `s-${item.id}`;
          return item;
        });
        return this.secondGroup;
      } catch (error) { }
    },
    initDisabledPro () {
      this.proData.forEach((item) => {
        item.children.forEach((ele) => {
          /* 处理二级禁用 */
          if (
            ele.children.every(child =>
              this.disabledProList.includes(child.id)
            )
          ) {
            ele.disabled = true;
          }
          ele.children.forEach(child => {
            child.disabled = this.disabledProList.includes(child.id);
          });
        });
        /* 处理一级禁用 */
        if (item.children.every(ele => ele.disabled)) {
          item.disabled = true;
        }
      });
    },
    /* 组装数据 */
    buildProData () {
      if (this.isOnlyGroup) {
        // 只显示分组
        this.proData = this.firstGroup
          .map((item) => {
            let secondArr = [];
            this.secondGroup.forEach((sItem) => {
              if (sItem.parent_id === item.id) {
                secondArr.push(sItem);
              }
            });
            item.children = secondArr;
            return item;
          })
          .filter((item) => {
            return item.children.length > 0;
          });
      } else {
        // 显示分组和商品
        const fArr = this.firstGroup.map((item) => {
          let secondArr = [];
          this.secondGroup.forEach((sItem) => {
            if (sItem.parent_id === item.id) {
              secondArr.push(sItem);
            }
          });
          item.children = secondArr;
          return item;
        });

        setTimeout(() => {
          const temp = fArr.map((item) => {
            item.children.map((ele) => {
              let temp = [];
              this.proList.forEach((e) => {
                if (e.product_group_id_second === ele.id) {
                  temp.push(e);
                }
              });
              ele.children = temp;
              return ele;
            });
            return item;
          });
          // 过滤无子项数据
          this.proData = temp.filter((item) => {
            return (
              item.children.length > 0 &&
              item.children.some((el) => {
                return el.children.length > 0;
              })
            );
          });
          this.initDisabledPro();
        }, 0);
      }
    },
    init () {
      try {
        // 标记正在加载
        sharedDataCache.loading = true;
        // 获取商品，一级，二级分组
        sharedDataCache.loadingPromise = Promise.all([
          this.getProList(),
          this.getFirPro(),
          this.getSecPro(),
        ]).then((res) => {
          // 缓存数据
          sharedDataCache.proList = res[0];
          sharedDataCache.firstGroup = res[1];
          sharedDataCache.secondGroup = res[2];
          sharedDataCache.loading = false;
          
          this.proList = res[0];
          this.firstGroup = res[1];
          this.secondGroup = res[2];
          
          this.buildProData();
        }).catch((error) => {
          sharedDataCache.loading = false;
          this.$message.error(error.data.msg);
        });
      } catch (error) {
        sharedDataCache.loading = false;
        this.$message.error(error.data.msg);
      }
    },
  },
};

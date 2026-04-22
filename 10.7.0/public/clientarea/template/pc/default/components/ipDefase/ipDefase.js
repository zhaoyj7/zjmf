const ipDefase = {
  template: /*html*/ ` 
<div>

  <div class="ip-defase-box">
    <div class="fire-list-search">
      <el-select v-model="params.status" clearable :placeholder="lang.ipDefase_text1">
        <el-option v-for="item in statusOptions" :key="item.value" :label="item.label" :value="item.value">
        </el-option>
      </el-select>
      <el-input v-model="params.keywords" clearable @keyup.enter.native="search" :placeholder="lang.ipDefase_text2">
      </el-input>
      <el-button type="primary"  @click="search">{{lang.ipDefase_text3}}</el-button>
    </div>
    <el-table v-loading="tabeLoading" :data="productList" @sort-change="sortChange"
      style="width: 100%; margin:0.2rem 0;">
      <el-table-column prop="id" label="ID">
      </el-table-column>
      <el-table-column prop="host_ip" :label="lang.ipDefase_text4" sortable>
      </el-table-column>
      <el-table-column prop="defense_peak" :label="lang.ipDefase_text14">
        <template slot-scope="{row}">
          <span>{{row.defense_peak || '--'}}</span>
        </template>
      </el-table-column>
      <el-table-column prop="due_time" :label="lang.index_text13">
      <template slot-scope="{row}">
        <span>{{row.due_time | formateTime }}</span>
      </template>
      </el-table-column>
      <el-table-column prop="status" :label="lang.ipDefase_text5" width="200">
        <template slot-scope="{row}">
          <template v-if="row.statusLoading">
            <i class="primary-icon el-icon-loading"></i>
          </template>
          <template v-else>
            {{getStatusLabel(row.status)}}
            <i @click="getProductStatus(row)" class="primary-icon el-icon-refresh"></i>
          </template>
        </template>
      </el-table-column>
      <el-table-column prop="op" :label="lang.ipDefase_text6" width="160" fixed="right">
        <template slot-scope="{row}">
          <div class="operation">
            <el-button type="text" @click="showRenew(row)" v-if="row.sub_host_id">{{lang.cloud_re_btn}}</el-button>
            <el-button type="text" @click="openUpgradeDialog(row)">{{lang.ipDefase_text7}}</el-button>
          </div>
        </template>
      </el-table-column>
    </el-table>
    <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange"></pagination>
  </div>

  <div class="upgrade-dialog" v-if="isShowUpgrade">
    <el-dialog width="9.5rem" :visible.sync="isShowUpgrade" :show-close="false" @close="upgradeDgClose">
      <div class="dialog-title">{{lang.ipDefase_text11}}</div>
      <div class="dialog-main">
        <div class="ipDefase-now-info">
          <div class="now-text">{{lang.ipDefase_text15}}：{{ipInfo.host_ip}}</div>
          <div class="now-text">{{lang.ipDefase_text16}}：{{getStatusLabel(ipInfo.status)}}</div>
          <div class="now-text">{{lang.ipDefase_text17}}：{{ipInfo.defense_peak || '--'}}</div>
        </div>
        <el-form ref="ipDefaseForm" label-position="left" label-width="100px" hide-required-asterisk>
          <!-- 防御 -->
          <el-form-item :label="lang.ipDefase_text12">
            <el-radio-group v-model="defenseName">
              <el-radio-button :label="c.desc" v-for="(c,cInd) in defenceList" :key="cInd"  @click.native="chooseDefence($event,c)">
              </el-radio-button>
            </el-radio-group>
          </el-form-item>
          <el-form-item :label="lang.cart_tip_text12" v-if="upDurationList.length > 0">
            <el-radio-group v-model="upParams.duration_id">
              <el-radio-button :label="c.id" v-for="c in upDurationList" :key="c.id"  @click.native="changeDuration($event,c)">
                {{c.name_show}}
              </el-radio-button>
            </el-radio-group>
          </el-form-item>
        </el-form>
      </div>
      <div class="dialog-footer">
        <div class="footer-top">
          <div class="money-text">{{lang.ipDefase_text13}}：</div>
          <div class="money" v-loading="upgradePriceLoading">
            <span class="money-num">{{commonData.currency_prefix }} {{ upParams.totalPrice |  filterMoney}}</span>
            <el-popover placement="top-start" width="200" trigger="hover"
              v-if="isShowLevel || (isShowPromo && upParams.isUseDiscountCode)">
              <div class="show-config-list">
                <p v-if="isShowLevel">
                  {{lang.shoppingCar_tip_text2}}：{{commonData.currency_prefix}}
                  {{ upParams.clDiscount | filterMoney }}
                </p>
                <p v-if="isShowPromo && upParams.isUseDiscountCode">
                  {{lang.shoppingCar_tip_text4}}：{{commonData.currency_prefix}}
                  {{ upParams.code_discount | filterMoney}}
                </p>
              
              </div>
              <i class="el-icon-warning-outline total-icon" slot="reference"></i>
            </el-popover>
            <p class="original-price" v-if="upParams.totalPrice != upParams.original_price">
              {{commonData.currency_prefix}} {{ upParams.original_price |
                    filterMoney}}
            </p>
            <div class="code-box" v-if="false">
              <!-- 优惠码 -->
              <discount-code v-show="isShowPromo && !upParams.customfield.promo_code  "
                @get-discount="getUpDiscount(arguments)" scene="upgrade" :product_id="product_id"
                :amount="upParams.original_price" :billing_cycle_time="billing_cycle_time">
              </discount-code>
            </div>
            <div class="code-number-text">
              <div class="discount-codeNumber" v-show="upParams.customfield.promo_code">
                {{ upParams.customfield.promo_code }}<i class="el-icon-circle-close remove-discountCode"
                  @click="removeUpDiscountCode()"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="footer-bottom">
          <el-button class="btn-ok" @click="upgradeSub" :loading="loading4">
            {{lang.security_btn5}}
          </el-button>
          <div class="btn-no" @click="upgradeDgClose">
            {{lang.security_btn6}}
          </div>
        </div>
      </div>
    </el-dialog>
  </div>


  <!-- 续费弹窗 -->
  <div class="renew-dialog">
  <el-dialog width="6.9rem" :visible.sync="isShowRenew" :show-close="false" @close="renewDgClose">
    <div class="dialog-title">{{lang.common_cloud_title10}}</div>
    <div class="dialog-main">
      <div class="renew-content">
        <div class="renew-item" :class="renewActiveId==item.id?'renew-active':''" v-for="item in renewPageData"
          :key="item.id" @click="renewItemChange(item)">
          <div class="item-top">{{item.customfield?.multi_language?.billing_cycle || item.billing_cycle}}</div>
          <div class="item-bottom" v-if="isShowPromo && renewParams.isUseDiscountCode">
            {{commonData.currency_prefix + item.base_price}}
          </div>
          <div class="item-bottom" v-else>
            {{commonData.currency_prefix + item.price}}
          </div>
          <div class="item-origin-price"
            v-if="item.price*1 < item.base_price*1 && !renewParams.isUseDiscountCode">
            {{commonData.currency_prefix + item.base_price}}
          </div>
          <i class="el-icon-check check" v-show="renewActiveId==item.id"></i>
        </div>
      </div>
      <div class="pay-content">
        <div class="pay-price">
          <div class="money" v-loading="renewLoading">
            <span class="text">{{lang.common_cloud_label11}}:</span>
            <span>{{commonData.currency_prefix}}{{renewParams.totalPrice |
              filterMoney}}</span>
            <el-popover placement="top-start" width="200" trigger="hover"
              v-if="(isShowLevel && renewParams.clDiscount*1 > 0) || (isShowPromo && renewParams.isUseDiscountCode)">
              <div class="show-config-list">
                <p v-if="isShowLevel && renewParams.clDiscount*1 > 0">
                  {{lang.shoppingCar_tip_text2}}：{{commonData.currency_prefix}}
                  {{ renewParams.clDiscount | filterMoney}}
                </p>
                <p v-if="isShowPromo && renewParams.isUseDiscountCode">
                  {{lang.shoppingCar_tip_text4}}：{{commonData.currency_prefix}}
                  {{ renewParams.code_discount | filterMoney }}
                </p>
              </div>
              <i class="el-icon-warning-outline total-icon" slot="reference"></i>
            </el-popover>
            <p class="original-price"
              v-if="renewParams.customfield.promo_code && renewParams.totalPrice != renewParams.base_price">
              {{commonData.currency_prefix}} {{ renewParams.base_price |
              filterMoney}}
            </p>
            <p class="original-price"
              v-if="!renewParams.customfield.promo_code && renewParams.totalPrice != renewParams.original_price">
              {{commonData.currency_prefix}} {{ renewParams.original_price
              | filterMoney}}
            </p>
            <div class="code-box" v-if="false">
              <!-- 优惠码 -->
              <discount-code v-show="isShowPromo && !renewParams.customfield.promo_code"
                @get-discount="getRenewDiscount(arguments)" scene="renew" :product_id="product_id"
                :amount="renewParams.base_price" :billing_cycle_time="renewParams.duration">
              </discount-code>
            </div>
            <div class="code-number-text">
              <div class="discount-codeNumber" v-show="renewParams.customfield.promo_code">
                {{ renewParams.customfield.promo_code }}<i class="el-icon-circle-close remove-discountCode"
                  @click="removeRenewDiscountCode()"></i>
              </div>
            
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="dialog-footer">
      <el-button class="btn-ok" @click="subRenew" :loading="subRenewLoading">
        {{lang.common_cloud_btn30}}
      </el-button>
      <el-button class="btn-no" @click="renewDgClose">
        {{lang.common_cloud_btn29}}
      </el-button>
    </div>
  </el-dialog>
</div>


  <pay-dialog ref="ipDefasePayDialog" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>

</div>

          `,
  data() {
    return {
      commonData: {},
      params: {
        keywords: "",
        status: "",
        page: 1,
        limit: 20,
        pageSizes: [20, 50, 100],
        total: 0,
        orderby: "id",
        sort: "desc",
      },
      tabeLoading: false,
      statusOptions: [
        {label: lang.ipDefase_text8, value: 0},
        {label: lang.ipDefase_text9, value: 1},
        {label: lang.ipDefase_text10, value: 2},
      ],
      productList: [],
      isShowUpgrade: false,
      isShowLevel: false,
      isShowPromo: false,
      upParams: {
        customfield: {
          promo_code: "", // 优惠码
        },
        duration_id: "", // 周期
        isUseDiscountCode: false, // 是否使用优惠码
        clDiscount: 0, // 用户等级折扣价
        code_discount: 0, // 优惠码折扣价
        original_price: 0, // 原价
        totalPrice: 0, // 现价
      },
      upgradePriceLoading: false,
      loading4: false,

      defenceList: [],
      peak_defence: "",
      defenseName: "",
      ipInfo: {},
      ip: "",
      isInit: true,
      isShowRenew: false, // 续费的总计loading
      renewBtnLoading: false, // 续费按钮的loading
      // 续费页面信息
      renewPageData: [],
      renewPriceList: [],
      renewActiveId: "",
      renewLoading: false, // 续费计算折扣loading
      // 续费参数
      renewParams: {
        id: 0, //默认选中的续费id
        isUseDiscountCode: false, // 是否使用优惠码
        customfield: {
          promo_code: "", // 优惠码
        },
        duration: "", // 周期
        billing_cycle: "", // 周期时间
        clDiscount: 0, // 用户等级折扣价
        code_discount: 0, // 优惠码折扣价
        original_price: 0, // 原价
        base_price: 0,
        totalPrice: 0, // 现价
      },
      sub_host_id: 0,
      subRenewLoading: false,
      upDurationList: [],
    };
  },
  components: {
    payDialog,
    pagination,
    discountCode,
    cashBack,
  },
  props: {
    id: {
      // 产品id
      type: String | Number,
      required: true,
    },
    // 场景中的所有商品ID
    product_id: {
      type: String | Number,
      required: true,
    },
    billing_cycle_time: {
      type: String | Number,
      required: true,
    },
    module: {
      type: String,
      required: true,
    },
    listmode: {
      type: String,
      required: true,
    },
    showip: {
      type: String,
      required: false,
    },
  },
  watch: {
    renewParams: {
      handler() {
        let n = 0;
        // l:当前周期的续费价格
        if (this.isShowPromo && this.renewParams.customfield.promo_code) {
          // n: 算出来的价格
          n =
            (this.renewParams.base_price * 1000 -
              this.renewParams.clDiscount * 1000 -
              this.renewParams.code_discount * 1000) /
              1000 >
            0
              ? (this.renewParams.base_price * 1000 -
                  this.renewParams.clDiscount * 1000 -
                  this.renewParams.code_discount * 1000) /
                1000
              : 0;
        } else {
          //  n: 算出来的价格
          n =
            (this.renewParams.original_price * 1000 -
              this.renewParams.clDiscount * 1000 -
              this.renewParams.code_discount * 1000) /
              1000 >
            0
              ? (this.renewParams.original_price * 1000 -
                  this.renewParams.clDiscount * 1000 -
                  this.renewParams.code_discount * 1000) /
                1000
              : 0;
        }
        let t = n;
        // 如果当前周期和选择的周期相同，则和当前周期对比价格
        // if (
        //   this.hostData.billing_cycle_time === this.renewParams.duration ||
        //   this.hostData.billing_cycle_name === this.renewParams.billing_cycle
        // ) {
        //   // 谁大取谁
        //   t = n;
        // }
        this.renewParams.totalPrice =
          t * 1000 > 0 ? ((t * 1000) / 1000).toFixed(2) : 0;
      },
      immediate: true,
      deep: true,
    },
  },
  created() {
    this.getProductList();
    this.getCommonData();
  },
  mounted() {
    this.isShowLevel = havePlugin("IdcsmartClientLevel");
    this.isShowPromo = havePlugin("PromoCode");
  },
  filters: {
    formateTime(time) {
      if (time && time !== 0) {
        return formateDate(time * 1000);
      } else {
        return "--";
      }
    },
    // 返回剩余到期时间
    formateDueDay(time) {
      return Math.floor((time * 1000 - Date.now()) / (1000 * 60 * 60 * 24));
    },
    filterMoney(money) {
      if (isNaN(money) || money * 1 < 0) {
        return "0.00";
      } else {
        return formatNuberFiexd(money);
      }
    },
  },
  methods: {
    paySuccess(e) {
      this.isShowUpgrade = false;
      this.$emit("success");
      setTimeout(() => {
        this.getProductList();
      }, 1000);
    },
    // 取消支付回调
    payCancel(e) {},
    openUpgradeDialog(row) {
      this.ipInfo = row;
      this.ip = row.host_ip;
      this.getUpConfig();
    },
    getUpConfig() {
      apiGetUpDefenceConfig(
        {
          id: this.id,
          ip: this.ip,
        },
        this.module
      )
        .then((res) => {
          this.defenceList = res.data.data?.defence || [];
          this.peak_defence = res.data.data?.current_defence
            ? res.data.data?.current_defence
            : res.data.data?.defence[0]?.value || "";
          this.defenseName = this.defenceList.find(
            (item) => item.value === this.peak_defence
          )?.desc;
          this.isShowUpgrade = true;
          this.getCycleList();
        })
        .catch((err) => {
          err.data.msg && this.$message.error(err.data.msg);
        });
    },
    chooseDefence(e, c) {
      this.defenseName = c.desc;
      this.peak_defence = c.value;
      this.getCycleList();
      e.preventDefault();
    },
    changeDuration(e, c) {
      this.upParams.duration_id = c.id;
      this.getCycleList();
      e.preventDefault();
    },
    // 关闭升降级弹窗
    upgradeDgClose() {
      this.isShowUpgrade = false;
      this.removeUpDiscountCode(false);
    },

    // 升降级使用优惠码
    getUpDiscount(data) {
      this.upParams.customfield.promo_code = data[1];
      this.upParams.isUseDiscountCode = true;
      this.upParams.code_discount = Number(data[0]);
      this.getCycleList();
    },
    // 移除升降级优惠码
    removeUpDiscountCode(flag = true) {
      this.upParams.isUseDiscountCode = false;
      this.upParams.customfield.promo_code = "";
      this.upParams.code_discount = 0;
      if (flag) {
        this.getCycleList();
      }
    },

    // 获取升降级价格
    getCycleList() {
      this.upgradePriceLoading = true;
      const params = {
        id: this.id,
        ip: this.ip,
        peak_defence: this.peak_defence,
        duration_id: this.upParams.duration_id,
      };
      apiCalculateUpDefencePrice(params, this.module)
        .then(async (res) => {
          if (res.data.status == 200) {
            this.upDurationList = res.data.data.durations;
            if (!this.upParams.duration_id) {
              this.upParams.duration_id =
                res.data.data.duration_id || this.upDurationList[0]?.id;
            }
            if (res.data.data.durations?.length === 0) {
              this.upParams.duration_id = "";
            }
            let price = res.data.data.price; // 当前产品的价格
            if (price < 0) {
              this.upParams.original_price = 0;
              this.upParams.totalPrice = 0;
              this.upgradePriceLoading = false;
              return;
            }
            this.upParams.original_price = price;
            this.upParams.totalPrice = price;
            // 开启了等级优惠
            if (this.isShowLevel) {
              await clientLevelAmount({id: this.product_id, amount: price})
                .then((ress) => {
                  this.upParams.clDiscount = Number(ress.data.data.discount);
                })
                .catch(() => {
                  this.upParams.clDiscount = 0;
                });
            }
            // 开启了优惠码插件
            if (this.isShowPromo) {
              // 更新优惠码
              await applyPromoCode({
                // 开启了优惠券
                scene: "upgrade",
                product_id: this.product_id,
                amount: price,
                billing_cycle_time: this.billing_cycle_time,
                promo_code: this.upParams.customfield.promo_code,
                host_id: this.id,
              })
                .then((resss) => {
                  this.upParams.isUseDiscountCode = true;
                  this.upParams.code_discount = Number(
                    resss.data.data.discount
                  );
                })
                .catch((err) => {
                  this.upParams.isUseDiscountCode = false;
                  this.upParams.customfield.promo_code = "";
                  this.upParams.code_discount = 0;
                  this.$message.error(err.data.msg);
                });
            }
            this.upParams.totalPrice =
              (price * 1000 -
                this.upParams.clDiscount * 1000 -
                this.upParams.code_discount * 1000) /
                1000 >
              0
                ? (
                    (price * 1000 -
                      this.upParams.clDiscount * 1000 -
                      this.upParams.code_discount * 1000) /
                    1000
                  ).toFixed(2)
                : 0;
            this.upgradePriceLoading = false;
          } else {
            this.upParams.original_price = 0;
            this.upParams.clDiscount = 0;
            this.upParams.isUseDiscountCode = false;
            this.upParams.customfield.promo_code = "";
            this.upParams.code_discount = 0;
            this.upParams.totalPrice = 0;
            this.upgradePriceLoading = false;
          }
        })
        .catch((error) => {
          this.upDurationList = [];
          this.upParams.duration_id = "";
          this.upParams.original_price = 0;
          this.upParams.clDiscount = 0;
          this.upParams.isUseDiscountCode = false;
          this.upParams.customfield.promo_code = "";
          this.upParams.code_discount = 0;
          this.upParams.totalPrice = 0;
          this.upgradePriceLoading = false;
        });
    },

    // 升降级提交
    upgradeSub() {
      const params = {
        id: this.id,
        ip: this.ip,
        peak_defence: this.peak_defence,
        duration_id: this.upParams.duration_id,
        customfield: this.upParams.customfield,
      };
      this.loading4 = true;
      apiGenerateUpDefenceOrder(params, this.module)
        .then((res) => {
          if (res.data.status === 200) {
            this.$message.success(lang.common_cloud_text56);
            const orderId = res.data.data.id;
            // 调支付弹窗
            this.$refs.ipDefasePayDialog.showPayDialog(orderId, 0);
          } else {
            this.$message.error(err.data.msg);
          }
        })
        .catch((err) => {
          this.$message.error(err.data.msg);
        })
        .finally(() => {
          this.loading4 = false;
        });
    },

    goProductDetail(row) {
      window.open(`/productdetail.htm?id=${row.host_id}&showUp=1`);
    },
    getStatusLabel(status) {
      return (
        this.statusOptions.find((item) => item.value === status)?.label || "--"
      );
    },
    search() {
      this.params.page = 1;
      this.getProductList();
    },
    // 每页展示数改变
    sizeChange(e) {
      this.params.limit = e;
      this.params.page = 1;
      // 获取列表
      this.getProductList();
    },
    // 当前页改变
    currentChange(e) {
      this.params.page = e;
      this.getProductList();
    },
    sortChange({prop, order}) {
      this.params.orderby = order ? prop : "id";
      this.params.sort = order === "ascending" ? "asc" : "desc";
      this.getProductList();
    },
    // 获取产品状态
    getProductStatus(item, isInit = false) {
      !isInit && (item.statusLoading = true);
      apiProductRefreshHostIpStatus({id: item.id}, this.listmode)
        .then((res) => {
          item.status = res.data.data.status;
          if (item.statusLoading) {
            this.$message.success(res.data.msg);
          }
          item.statusLoading = false;
        })
        .catch((err) => {
          item.statusLoading = false;
          this.$message.error(err.data.msg);
        });
    },
    getProductList() {
      this.tabeLoading = true;
      apiProductGetHostIp({...this.params, host_id: this.id}, this.listmode)
        .then(async (res) => {
          this.tabeLoading = false;
          this.productList = res.data.data.list.map((item) => {
            item.statusLoading = false;
            this.getProductStatus(item, true);
            return item;
          });
          this.params.total = res.data.data.count;
          if (this.showip && this.isInit) {
            this.isInit = false;
            const ipInfo = this.productList.find(
              (item) => item.host_ip == this.showip
            );
            if (ipInfo) {
              this.openUpgradeDialog(ipInfo);
            }
          }
        })
        .catch((err) => {
          this.tabeLoading = false;
          this.$message.error(err.data.msg);
        });
    },
    getCommonData() {
      this.commonData = JSON.parse(localStorage.getItem("common_set_before"));
    },

    // 续费使用优惠码
    async getRenewDiscount(data) {
      this.renewParams.customfield.promo_code = data[1];
      this.renewParams.isUseDiscountCode = true;
      this.renewParams.code_discount = Number(data[0]);
      const price = this.renewParams.base_price;
      const discountParams = {id: this.product_id, amount: price};
      // 开启了等级折扣插件
      if (this.isShowLevel) {
        // 获取等级抵扣价格
        await clientLevelAmount(discountParams)
          .then((res2) => {
            if (res2.data.status === 200) {
              this.renewParams.clDiscount = Number(res2.data.data.discount); // 客户等级优惠金额
            }
          })
          .catch((error) => {
            this.renewParams.clDiscount = 0;
          });
      }
    },
    // 移除续费的优惠码
    removeRenewDiscountCode() {
      this.renewParams.isUseDiscountCode = false;
      this.renewParams.customfield.promo_code = "";
      this.renewParams.code_discount = 0;
      this.renewParams.clDiscount = 0;
      const price = this.renewParams.original_price;
    },

    // 显示续费弹窗
    showRenew(row) {
      this.sub_host_id = row.sub_host_id;
      if (this.renewBtnLoading) return;
      this.renewBtnLoading = true;
      // 获取续费页面信息
      const params = {
        id: this.sub_host_id,
      };
      this.isShowRenew = true;
      this.renewLoading = true;
      renewPage(params)
        .then((res) => {
          if (res.data.status === 200) {
            this.renewBtnLoading = false;
            this.renewPageData = res.data.data.host;
            this.renewActiveId = this.renewPageData[0].id;
            this.renewParams.billing_cycle =
              this.renewPageData[0].billing_cycle;
            this.renewParams.duration = this.renewPageData[0].duration;
            this.renewParams.original_price = this.renewPageData[0].price;
            this.renewParams.base_price = this.renewPageData[0].base_price;
          }
          this.renewLoading = false;
        })
        .catch((err) => {
          this.renewBtnLoading = false;
          this.renewLoading = false;
          this.$message.error(err.data.msg);
        });
    },
    getRenewPrice() {
      renewPage({id: this.id})
        .then(async (res) => {
          if (res.data.status === 200) {
            this.renewPriceList = res.data.data.host;
          }
        })
        .catch((err) => {
          this.renewPriceList = [];
        });
    },
    // 续费弹窗关闭
    renewDgClose() {
      this.isShowRenew = false;
      this.removeRenewDiscountCode();
    },
    // 续费提交
    subRenew() {
      this.subRenewLoading = true;
      const params = {
        id: this.sub_host_id,
        billing_cycle: this.renewParams.billing_cycle,
        customfield: this.renewParams.customfield,
      };

      renew(params)
        .then((res) => {
          this.subRenewLoading = false;
          if (res.data.status === 200) {
            this.$message.success(res.data.msg);
            this.isShowRenew = false;
            if (res.data.code == "Paid") {
              this.getProductList();
            } else {
              this.$refs.ipDefasePayDialog.showPayDialog(res.data.data.id);
            }
          }
        })
        .catch((err) => {
          this.subRenewLoading = false;
          this.$message.error(err.data.msg);
        });
    },
    // 续费周期点击
    async renewItemChange(item) {
      this.renewLoading = true;
      this.renewActiveId = item.id;
      this.renewParams.duration = item.duration;
      this.renewParams.billing_cycle = item.billing_cycle;
      this.renewParams.original_price = item.price;
      this.renewParams.base_price = item.base_price;

      // 开启了优惠码插件
      if (this.isShowPromo && this.renewParams.isUseDiscountCode) {
        const discountParams = {id: this.product_id, amount: item.base_price};
        // 开启了等级折扣插件
        if (this.isShowLevel) {
          // 获取等级抵扣价格
          await clientLevelAmount(discountParams)
            .then((res2) => {
              if (res2.data.status === 200) {
                this.renewParams.clDiscount = Number(res2.data.data.discount); // 客户等级优惠金额
              }
            })
            .catch((error) => {
              this.renewParams.clDiscount = 0;
            });
        }

        // 更新优惠码
        await applyPromoCode({
          scene: "renew",
          product_id: this.product_id,
          amount: item.base_price,
          billing_cycle_time: this.renewParams.duration,
          promo_code: this.renewParams.customfield.promo_code,
        })
          .then((resss) => {
            price = item.base_price;
            this.renewParams.isUseDiscountCode = true;
            this.renewParams.code_discount = Number(resss.data.data.discount);
          })
          .catch((err) => {
            this.$message.error(err.data.msg);
            this.removeRenewDiscountCode();
          });
      }
      this.renewLoading = false;
    },
  },
};

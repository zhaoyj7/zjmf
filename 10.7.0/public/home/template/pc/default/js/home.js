(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = window.lang;

    new Vue({
      components: {
        asideMenu,
        topMenu,
        rechargeDialog,
        payDialog,
        creditNotice,
      },
      created() {
        localStorage.frontMenusActiveId = "";
        this.getCommonData();
        this.getGateway();
      },
      mounted() {
        const addons = document.querySelector("#addons_js");
        this.addons_js_arr = JSON.parse(addons.getAttribute("addons_js"));
        this.initData();
      },
      updated() {},
      destroyed() {},
      data() {
        return {
          addons_js_arr: [], // 插件数组
          commonData: {
            currency_prefix: "￥",
          },
          idcsmart_client_level: {
            name: "",
            id: "",
            background_color: "",
          },
          showRight: false,
          account: {}, // 个人信息
          certificationObj: {}, // 认证信息
          percentage: 0,
          productListLoading: true,
          nameLoading: false,
          infoSecLoading: false,
          productList: [], // 产品列表
          ticketList: [], // 工单列表
          homeNewList: [], // 新闻列表
          // 支付方式
          gatewayList: [],
          headBgcList: [
            "#3699FF",
            "#57C3EA",
            "#5CC2D7",
            "#EF8BA2",
            "#C1DB81",
            "#F1978C",
            "#F08968",
          ],
          // 轮询相关
          timer: null,
          time: 300000,
          // 后台返回的支付html
          payHtml: "",
          // 错误提示信息
          errText: "",
          // 是否显示充值弹窗
          isShowCz: false,
          payLoading1: false,
          isShowimg1: true,
          // 充值弹窗表单数据
          czData: {
            amount: "",
            gateway: "",
          },
          czDataOld: {
            amount: "",
            gateway: "",
          },
          isOpen: true,
          promoterData: {},
          openVisible: false,
          voucherList: [], // 可领代金券列表
          hasWxPlugin: false,
          wxQrcode: "",
          conectInfo: {
            is_subscribe: 0,
            accept_push: 0,
          },
          codeLoading: false,
          isShowCredit: false,
          creditData: {},
          coinData: {},
          coinRecharge: [],
        };
      },
      filters: {
        formateTime(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000);
          } else {
            return "--";
          }
        },
        formareDay(time) {
          if (time && time !== 0) {
            const dataTime = formateDate(time * 1000);
            return (
              dataTime.split(" ")[0].split("-")[1] +
              "-" +
              dataTime.split(" ")[0].split("-")[2]
            );
          } else {
            return "--";
          }
        },
      },
      methods: {
        goOrderList(val) {
          location.href = "/finance.htm?order_status=" + val;
        },
        goProductList(val) {
          if (val) {
            location.href = "/productList.htm?tab=" + val;
            return;
          }
          location.href = "/productList.htm";
        },
        //  授信详情
        getCreditDetail() {
          creditDetail().then((res) => {
            if (res.data.status === 200) {
              this.isShowCredit = true;
              this.creditData = res.data.data.credit_limit;
            }
          });
        },
        goCredit() {
          location.href = "/finance.htm?tab=6";
        },
        async getWxConectInfo() {
          try {
            const res = await getWxInfo();
            this.conectInfo = res.data.data;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        async getWxcode() {
          try {
            this.codeLoading = true;
            const res = await getWxQrcode();
            this.wxQrcode = res.data.data.img_url;
            this.codeLoading = false;
          } catch (error) {
            this.codeLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        /* 可领代金券 */
        async getVoucherAvailable() {
          try {
            const res = await voucherAvailable({page: 1, limit: 999});
            this.voucherList = res.data.data.list.filter(
              (item) => !item.is_get
            );
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        /* 可领代金券 end */
        toReferral() {
          location.href = `/plugin/${getPluginId(
            "IdcsmartRecommend"
          )}/recommend.htm`;
        },
        handelAttestation() {
          location.href = `/plugin/${getPluginId(
            "IdcsmartCertification"
          )}/authentication_select.htm`;
        },
        goWorkPage() {
          location.href = `/plugin/${getPluginId("IdcsmartTicket")}/ticket.htm`;
        },
        goNoticePage() {
          location.href = `/plugin/${getPluginId("IdcsmartNews")}/source.htm`;
        },
        goNoticeDetail(id) {
          location.href = `/plugin/${getPluginId(
            "IdcsmartNews"
          )}/news_detail.htm?id=${id}`;
        },
        goGoodsList() {
          location.href = `/cart/goodsList.htm`;
        },
        goProductPage(id) {
          location.href = `/productdetail.htm?id=${id}`;
        },
        goTickDetail(orderid) {
          location.href = `/plugin/${getPluginId(
            "IdcsmartTicket"
          )}/ticketDetails.htm?id=${orderid}`;
        },
        getCoinRecharge() {
          apiCoinRecharge().then((res) => {
            this.coinRecharge = res.data.data.coins;
          });
        },
        initData() {
          const arr = this.addons_js_arr.map((item) => {
            return item.name;
          });
          if (arr.includes("IdcsmartVoucher")) {
            this.getVoucherAvailable();
          }
          if (arr.includes("IdcsmartCertification")) {
            certificationInfo().then((res) => {
              this.certificationObj = res.data.data;
            });
          }
          if (arr.includes("IdcsmartTicket")) {
            ticket_list({page: 1, limit: 3}).then((res) => {
              this.ticketList = res.data.data.list;
            });
          }
          if (arr.includes("IdcsmartNews")) {
            newsList({page: 1, limit: 3}).then((res) => {
              this.homeNewList = res.data.data.list.slice(0, 3);
            });
          }
          if (arr.includes("IdcsmartRecommend")) {
            this.showRight = true;
            this.getPromoterInfo();
          }

          if (arr.includes("MpWeixinNotice")) {
            this.hasWxPlugin = true;
            this.getWxConectInfo();
          }

          if (arr.includes("CreditLimit")) {
            // 开启了信用额
            this.getCreditDetail();
          }

          if (arr.includes("Coin")) {
            this.getCoinDetail();
            this.getCoinRecharge();
          }

          this.getIndexHost();
          this.getIndexInfo();

          // promoter_statistic().then((res) => {
          //     console.log(res);
          // })
        },
        getCoinDetail() {
          apiCoinDetail().then((res) => {
            this.coinData = res.data.data;
          });
        },
        getIndexInfo() {
          this.nameLoading = true;
          indexData()
            .then((res) => {
              this.account = res.data.data.account;
              this.idcsmart_client_level =
                res.data.data.account.customfield?.idcsmart_client_level || {};
              localStorage.lang = res.data.data.account.language || "zh-cn";
              const reg = /^[a-zA-Z]+$/;
              if (reg.test(res.data.data.account.username.substring(0, 1))) {
                this.account.firstName = res.data.data.account.username
                  .substring(0, 1)
                  .toUpperCase();
              } else {
                this.account.firstName =
                  res.data.data.account.username.substring(0, 1);
              }
              this.percentage =
                (Number(this.account.this_month_consume) /
                  Number(this.account.consume)) *
                  100 || 0;
              if (sessionStorage.headBgc) {
                this.$refs.headBoxRef.style.background = sessionStorage.headBgc;
              } else {
                const index = Math.round(
                  Math.random() * (this.headBgcList.length - 1)
                );
                this.$refs.headBoxRef.style.background =
                  this.headBgcList[index];
                sessionStorage.headBgc = this.headBgcList[index];
              }
              this.nameLoading = false;
            })
            .catch((error) => {
              // jwt过期跳转订购产品页面
              // if (error.data.status == 401) {
              //     location.href = "login.htm"
              // }
            });
        },
        getIndexHost() {
          indexHost({page: 1, limit: 10})
            .then((res) => {
              this.productListLoading = false;
              this.productList = res.data.data.list;
              const data = new Date().getTime() * 0.001;
              this.productList.forEach((item) => {
                if (
                  item.due_time !== 0 &&
                  (item.due_time - data) / (60 * 60 * 24) <= 10
                ) {
                  item.isOverdue = true;
                } else {
                  item.isOverdue = false;
                }
              });
            })
            .catch(() => {
              this.productListLoading = false;
            });
        },
        // 获取支付方式列表
        getGateway() {
          gatewayList().then((res) => {
            if (res.data.status === 200) {
              this.gatewayList = res.data.data.list;
            }
          });
        },
        goUser() {
          location.href = `account.htm`;
        },
        // 支付成功回调
        paySuccess(e) {
          indexData().then((res) => {
            this.account = res.data.data.account;
            this.account.firstName = res.data.data.account.username.substring(
              0,
              1
            );
            this.percentage =
              (Number(this.account.this_month_consume) /
                Number(this.account.consume)) *
                100 || 0;
          });
        },
        // 取消支付回调
        payCancel(e) {},

        // 显示充值 dialog
        showCz() {
          this.$refs.rechargeDialog.open();
        },

        rechargeSuccess() {
          this.paySuccess();
        },
        setAccoutCredit() {
          this.$refs.creditNotice.open();
        },

        // 获取通用配置
        async getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          ) || {
            currency_prefix: "￥",
          };
          const res = await getCommon();
          this.commonData = res.data.data;
          localStorage.setItem(
            "common_set_before",
            JSON.stringify(res.data.data)
          );
          document.title =
            this.commonData.website_name + "-" + lang.index_text33;
        },
        // 获取推广者基础信息
        getPromoterInfo() {
          promoterInfo()
            .then((res) => {
              if (res.data.status == 200) {
                this.promoterData = res.data.data.promoter;
                if (res.data.data.promoter.permission === 0) {
                  this.showRight = false;
                }
                if (
                  JSON.stringify(this.promoterData) == "{}" ||
                  !res.data.data.promoter.url
                ) {
                  this.isOpen = false;
                } else {
                  this.isOpen = true;
                }
              }
            })
            .catch((err) => {
              this.isOpen = false;
              this.showRight = false;
            });
        },
        // 开启推介计划
        openReferral() {
          openRecommend()
            .then((res) => {
              if (res.data.status == 200) {
                this.$message.success(res.data.msg);
                this.getPromoterInfo();
                this.openVisible = false;
              }
            })
            .catch((error) => {
              this.$message.error(error.data.msg);
            });
        },
        // 复制
        copyUrl(text) {
          if (navigator.clipboard && window.isSecureContext) {
            // navigator clipboard 向剪贴板写文本
            this.$message.success(lang.index_text32);
            return navigator.clipboard.writeText(text);
          } else {
            // 创建text area
            const textArea = document.createElement("textarea");
            textArea.value = text;
            // 使text area不在viewport，同时设置不可见
            document.body.appendChild(textArea);
            // textArea.focus()
            textArea.select();
            this.$message.success(lang.index_text32);
            return new Promise((res, rej) => {
              // 执行复制命令并移除文本框
              document.execCommand("copy") ? res() : rej();
              textArea.remove();
            });
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

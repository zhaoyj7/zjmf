(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const finance = document.getElementById("finance");
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        asideMenu,
        topMenu,
        pagination,
        payDialog,
        rechargeDialog,
        withdrawDialog,
        proofDialog,
        contractInfo
      },

      data() {
        return {
          isShowOrderController: false, // 是否展示订单记录
          isShowTransactionController: false, // 是否展示交易订单
          isShowBalance: false, // 是否展示余额订单
          // 交易记录 是否展示订单详情
          isDetail: false,
          // 后台返回的支付html
          payHtml: "",
          // 轮询相关
          timer: null,
          isShowCombine: false,
          isShowContract: false,
          loading7: false,
          dataList7: [],
          isShowCredit: false,
          isShowCertification: false,
          balanceTimer: null,
          isShowCash: false,
          time: 300000,
          // 支付方式
          gatewayList: [],
          // 错误提示信息
          errText: "",
          deleteObj: {},
          // 待退款金额
          unAmount: 0,
          commonData: {},
          // 货币前缀
          currency_prefix: "",
          // 货币后缀
          currency_code: "",
          // 用户余额
          balance: 0,
          // 支付弹窗相关 开始
          // 支付弹窗控制
          isShowZf: false,
          isShowDeOrder: false,
          // 是否展示第三方支付
          isShowPay: true,
          zfData: {
            // 订单id
            orderId: 0,
            // 订单金额
            amount: 0,
            checked: false,
            // 支付方式
            gateway: gatewayList.length > 0 ? gatewayList[0].name : "",
          },

          // 支付弹窗相关结束
          // 是否显示提现弹窗
          isShowTx: false,
          // 是否显示充值弹窗
          isShowCz: false,
          // 充值弹窗表单数据
          czData: {
            amount: "",
            gateway: "",
          },
          czDataOld: {
            amount: "",
            gateway: "",
          },
          // 提现弹窗表单数据
          txData: {
            method_id: "",
            account: "",
            card_number: "",
            name: "",
            amount: "",
            source: "credit",
          },
          rules: {
            amount: [
              { required: true, message: lang.index_text29, trigger: "blur" },
            ],
          },
          // 余额记录列表
          balanceType: {
            Recharge: { text: lang.finance_text8 },
            Applied: { text: lang.finance_text9 },
            Refund: { text: lang.finance_text10 },
            Withdraw: { text: lang.finance_text11 },
            Artificial: { text: lang.finance_text15 },
            Freeze: { text: lang.finance_text145 },
            Unfreeze: { text: lang.finance_text146 },
          },
          loading1: false,
          loading2: false,
          loading3: false,
          loading4: false,
          loading5: false,
          loading6: false,
          dataList1: [],
          dataList2: [],
          dataList3: [],
          dataList4: [],
          dataList5: [],
          dataList6: [],
          creditData: {},
          timerId1: null,
          timerId2: null,
          timerId3: null,
          params1: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
            status: "",
            type: "",
          },
          params2: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },
          params3: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
            type: "",
            start_time: "",
            end_time: "",
          },
          params4: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            keywords: "",
          },
          params6: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            keywords: "",
          },
          params7: {
            id: "",
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },
          date: [],
          activeIndex: "1",
          // 订单类型
          tipslist1: [
            {
              color: "#0058FF",
              name: lang.finance_text12,
              value: "new",
            },
            {
              color: "#3DD598",
              name: `${lang.finance_text13}/${lang.demand_text5}`,
              value: "renew",
            },
            {
              color: "#F0142F",
              name: `${lang.finance_text14}/${lang.demand_text6}`,
              value: "upgrade",
            },
            {
              color: "#F99600",
              name: lang.finance_text15,
              value: "artificial",
            },
            {
              color: "#9C27B0",
              name: lang.finance_text16,
              value: "recharge",
            },
          ],
          orderType: [
            {
              name: lang.finance_text12,
              value: "new",
            },
            {
              name: lang.finance_text13,
              value: "renew",
            },
            {
              name: lang.demand_text5,
              value: "on_demand",
            },
            {
              name: lang.finance_text14,
              value: "upgrade",
            },
            {
              name: lang.finance_text15,
              value: "artificial",
            },
            {
              name: lang.finance_text16,
              value: "recharge",
            },
            {
              name: lang.coin_text136,
              value: "change_billing_cycle",
            },
          ],
          orderTypeObj: {
            new: lang.finance_text12,
            renew: lang.finance_text13,
            upgrade: lang.finance_text14,
            artificial: lang.finance_text15,
            recharge: lang.finance_text16,
            combine: lang.finance_label23,
            credit_limit: lang.finance_label24,
            on_demand: lang.demand_text5,
            change_billing_cycle: lang.demand_text6,
          },
          // 订单详情 产品状态
          status: {
            Unpaid: lang.finance_text3,
            Pending: lang.finance_text88,
            Active: lang.finance_text89,
            Suspended: lang.finance_text90,
            Deleted: lang.finance_text91,
            Failed: lang.finance_text92,
          },
          credit_status: {
            Expired: lang.finance_text93,
            Overdue: lang.finance_text94,
            Active: lang.finance_text95,
            Suspended: lang.finance_text96,
          },

          // 提现规则
          ruleData: {
            method: [],
          },
          // 提现方式
          txway: {},
          payLoading: false,
          isShowimg: true,
          payLoading1: false,
          isShowimg1: true,
          isShowBackTop: false,
          scrollY: 0,
          isEnd: false,
          multipleSelection: [],
          isShowMore: false,
          showCard: false, // 是否显示银行卡提现规则
          statusList: [
            {
              label: lang.finance_text97,
              id: 0,
            },
            {
              label: lang.finance_text98,
              id: 1,
            },
            {
              label: lang.finance_text99,
              id: 2,
            },
            {
              label: lang.finance_text100,
              id: 3,
            },
          ],
          isdot: false, // 当提现成功后 在提现记录加一个提示的效果
          allLoading: false,
          // 代金卷
          /* 新增代金券 */
          vParams: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 0,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },
          availableParams: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 0,
          },
          voucherList: [],
          voucherAvailableList: [],
          dialogVisible: false,
          voucherLoading: false,
          diaLoading: false,
          isShowCancel: false,
          certificationObj: {},
          isCertification: false,
          creditStatusObj: {
            Outstanding: {
              label: lang.finance_text101,
              color: "rgba(117, 117, 117, 1)",
              background: "rgba(238, 238, 238, 1)",
            },
            Disbursed: {
              label: lang.finance_text102,
              color: "rgba(249, 150, 0, 1)",
              background: "rgba(249, 150, 0, 0.12)",
            },
            Repaid: {
              label: lang.finance_text103,
              color: "rgba(61, 213, 152, 1)",
              background: "rgba(61, 213, 152, 0.12)",
            },
            Overdue: {
              label: lang.finance_text104,
              color: "rgba(240, 20, 47, 1)",
              background: "rgba(240, 20, 47, 0.08)",
            },
          },
          contractStatusObj: {
            no_sign: {
              label: lang.finance_text105,
              color: "rgba(117, 117, 117, 1)",
              background: "rgba(117, 117, 117, 0)",
            },
            review: {
              label: lang.finance_text106,
              color: "rgba(249, 150, 0, 1)",
              background: "rgba(249, 150, 0, 0.12)",
            },
            complete: {
              label: lang.finance_text107,
              color: "rgba(61, 213, 152, 1)",
              background: "rgba(61, 213, 152, 0.12)",
            },
            wait_mail: {
              label: lang.finance_text108,
              color: "rgba(54, 153, 255, 1)",
              background: "rgba(54, 153, 255, 0.12)",
            },
            reject: {
              label: lang.finance_text109,
              color: "rgba(240, 20, 47, 1)",
              background: "rgba(240, 20, 47, 0.08)",
            },
            cancel: {
              label: lang.finance_text110,
              color: "rgba(117, 117, 117, 1)",
              background: "rgba(238, 238, 238, 1)",
            },
          },
          isShowKd: false,
          isShowInfoDia: false,
          isShowMailDia: false,
          isShowCreditDia: false,
          exportLoading: false,
          recData: {},
          infoFormData: {
            name: "",
            id_number: "",
            contact_phone: "",
            contact_email: "",
            contact_address: "",
            company_seal: "",
            company_seal_url: "",
            is_save: false
          },
          mailFormData: {
            id: "",
            rec_person: "",
            rec_address: "",
            rec_phone: "",
          },
          mailRules: {
            rec_person: [
              {
                required: true,
                message: lang.finance_text111,
                trigger: "blur",
              },
            ],
            rec_address: [
              {
                required: true,
                message: lang.finance_text112,
                trigger: "blur",
              },
            ],
            rec_phone: [
              {
                required: true,
                message: lang.finance_text113,
                trigger: "blur",
              },
            ],
          },
          infoRules: {
            name: [
              {
                required: true,
                message: lang.finance_text114,
                trigger: "blur",
              },
            ],
            id_number: [
              {
                required: true,
                message: lang.finance_text115,
                trigger: "blur",
              },
            ],
            contact_phone: [
              {
                required: true,
                message: lang.finance_text116,
                trigger: "blur",
              },
            ],
            contact_email: [
              {
                required: true,
                message: lang.finance_text117,
                trigger: "blur",
              },
            ],
            contact_address: [
              {
                required: true,
                message: lang.finance_text118,
                trigger: "blur",
              },
            ],
            company_seal: [
              {
                required: true,
                message: lang.finance_text156,
                trigger: "change",
              },
            ],
          },
          cancelId: "",
          isOpenwithdraw: 0,
          isShowPre: false,
          preData: {},
          submitLoading: false,
          useCredit: true,
          tab: "",
          accountData: {},
          freeze_credit: "0",
          isShowFreezeDia: false,
          freezeLoading: false,
          freezeList: [],
          isBatchDel: false,
          oderDelLoading: false,
          isShowCoin: false,
          coinData: {},
          coinParams: {
            page: 1,
            limit: 24,
            pageSizes: [24, 50, 100],
            total: 0,
          },
          coinType: "active",
          coinList: [],
          coinLoading: false,
          waitCoinTotal: 0,
          jwt: `Bearer ${localStorage.jwt}`,
          sealPreviewVisible: false, // 签章预览对话框控制
        };
      },
      mixins: [mixin],
      computed: {
        canUseBalance() {
          const balance =
            Number(this.balance) -
            Number(this.accountData?.customfield?.pending_amount || 0);
          return balance > 0 ? balance.toFixed(2) : 0;
        },
        isShowCoinDetail() {
          const index = this.coinList.findIndex((item) => item.isShow);
          const totalRow = Math.ceil(this.coinList.length / 3);
          const showRow = Math.ceil((index + 1) / 3);
          // 判断是否是最后一行
          return index !== -1 && showRow === totalRow;
        },
        isShowVoucherDetail() {
          const index = this.voucherList.findIndex((item) => item.isShow);
          const totalRow = Math.ceil(this.voucherList.length / 3);
          const showRow = Math.ceil((index + 1) / 3);
          return index !== -1 && showRow === totalRow;
        },
      },
      mounted() {
        // 关闭loading
        // document.getElementById('mainLoading').style.display = 'none';
        const params = getUrlParams();
        if (params.order_status) {
          this.params1.status = params.order_status;
        }
        this.tab = params.tab;

        const addons_js_arr = JSON.parse(
          document.querySelector("#addons_js").getAttribute("addons_js")
        ); // 插件列表
        const arr = addons_js_arr.map((item) => {
          return item.name;
        });
        // 开启提现
        if (arr.includes("IdcsmartWithdraw")) {
          this.getWithdrawConfig();
        }
        if (arr.includes("IdcsmartVoucher")) {
          // 开启了代金券
          this.isShowCash = true;
        }
        if (arr.includes("IdcsmartOrderCombine")) {
          // 开启了订单合并
          this.isShowCombine = true;
        }
        if (arr.includes("EContract")) {
          // 开启了电子合同
          this.isShowContract = true;
        }
        if (arr.includes("Coin")) {
          this.isShowCoin = true;
          this.getCoinData();
        }
        if (arr.includes("IdcsmartCertification")) {
          this.isShowCertification = true;
          // 开启了实名认证
          certificationInfo().then((ress) => {
            if (ress.data.status === 200) {
              this.certificationObj = ress.data.data;
              this.isCertification = ress.data.data.is_certification;
              // if (ress.data.data.is_certification) {
              //   if (ress.data.data.company.status === 1) {
              //     this.infoFormData.name = ress.data.data.company.certification_company
              //     this.infoFormData.id_number = ress.data.data.company.company_organ_code
              //   } else if (ress.data.data.person.status === 1) {
              //     this.infoFormData.name = ress.data.data.person.certification_name
              //     this.infoFormData.id_number = ress.data.data.person.card_number
              //   }
              // }
            }
          });
        }
        if (arr.includes("CreditLimit")) {
          // 开启了信用额
          this.isShowCredit = true;
          this.getCreditDetail();
        }
      },
      updated() {
        // 关闭loading
        // document.getElementById('mainLoading').style.display = 'none';
        // document.getElementsByClassName('template')[0].style.display = 'block'
      },
      destroyed() { },
      created() {
        // 订单记录列表
        // this.getorderList();
        this.getCommon();
        this.getAccount();
        this.getGateway();
        this.getUnAmount();
      },
      watch: {
        txData: {
          handler(newV, oldV) {
            let obj = this.ruleData.method.find(
              (item) => item.id == newV.method_id
            );
            if (obj && obj.name == lang.finance_text119) {
              this.showCard = true;
            } else {
              this.showCard = false;
            }
          },
          deep: true,
        },
      },
      filters: {
        formatNumber(money) {
          return formatMoneyNumber(money);
        },
        formateTime(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000);
          } else {
            return "--";
          }
        },
        formateTime1(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000, "YYYY-MM-DD HH:mm:ss");
          } else {
            return lang.voucher_effective;
          }
        },
        formateTime2(time) {
          if (time && time !== 0) {
            return formateDate1(time * 1000);
          } else {
            return "--";
          }
        },
        formateTime3(time) {
          if (time && time !== 0) {
            return formateDate2(time * 1000);
          } else {
            return "--";
          }
        },
      },
      methods: {
        showDetail(item) {
          if (item.use_detail_list.length === 0 && !item.is_loading) {
            item.is_loading = true;
            apiCoinUseDetail({ id: item.id })
              .then((res) => {
                item.use_detail_list = res.data.data.list;
              })
              .finally(() => {
                item.is_loading = false;
              });
          }
        },
        getCoinData() {
          apiCoinCoupon().then((res) => {
            this.coinData = res.data.data;
          });
        },
        getCoin(item) {
          item.get_loading = true;
          apiCoinGet({ id: item.id })
            .then((res) => {
              item.get_loading = false;
              this.$message.success(res.data.msg);
              this.initCoinPage();
              this.getCoinData();
            })
            .catch((err) => {
              item.get_loading = false;
              this.$message.error(err.data.msg);
            });
        },
        handelOpenFreeze() {
          this.getFreezeList();
          this.isShowFreezeDia = true;
        },
        getFreezeList() {
          this.freezeLoading = true;
          apiFreezeList()
            .then((res) => {
              this.freezeList = res.data.data.list;
            })
            .finally(() => {
              this.freezeLoading = false;
            });
        },
        freezeClose() {
          this.isShowFreezeDia = false;
        },
        refresh(bol, id) {
          if (bol) {
            this.$refs.payDialog.showPayDialog(id);
          }
          this.getorderList();
        },
        uploadProof(id) {
          this.orderId = id;
          this.$refs.proof.getOrderDetails(id);
        },

        /* 提前还款 */
        handlePre(row) {
          if (!row) {
            row = this.dataList6[0] || [];
          }
          this.isShowPre = true;
          this.preData = row;
        },
        preClose() {
          this.isShowPre = false;
        },
        async submitPre() {
          try {
            this.submitLoading = true;
            this.useCredit = false;
            const res = await prePayment();
            this.preClose();
            this.$refs.payDialog.showPayDialog(res.data.data.order_id);
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.useCredit = true;
            this.$message.error(error.data.msg);
          }
        },
        /* 提前还款 end */
        async getWithdrawConfig() {
          try {
            const res = await withdrawRule();
            this.isOpenwithdraw = res.data.data.status;
          } catch (error) { }
        },
        handelPayCredit(order_id) {
          this.useCredit = false;
          this.$refs.payDialog.showPayDialog(order_id);
        },
        handelApplyOrder() {
          if (this.isShowCertification && !this.isCertification) {
            this.$message.warning(lang.finance_text120);
            return;
          }
          this.handelApplyContract();
        },
        // 去实名认证
        handelAttestation() {
          location.href = `/plugin/${this.pluginId(
            "IdcsmartCertification"
          )}/authentication_select.htm`;
        },
        // 获取插件Id
        pluginId(name) {
          const addons_js_arr = JSON.parse(
            document.querySelector("#addons_js").getAttribute("addons_js")
          ); // 插件列表
          for (let index = 0; index < addons_js_arr.length; index++) {
            const element = addons_js_arr[index];
            if (name === element.name) {
              return element.id;
            }
          }
        },
        // 申请合同
        handelApplyContract() {
          location.href = `/plugin/${this.pluginId(
            "EContract"
          )}/applyContract.htm`;
        },
        // 下载电子合同
        async handelDownload(id) {
          try {
            const res = await downloadContract(id);
            window.open(res.data.data.url);
          } catch (err) {
            err.data?.msg && this.$message.error(err.data.msg);
          }
        },
        // 预览电子合同
        async handelPreview(id) {
          try {
            const res = await viewContract(id); // 返回的是下载链接
            window.open(res.data.data.url);
          } catch (err) {
            err.data?.msg && this.$message.error(err.data.msg);
          }
        },

        // 处理产品name
        handelHostName(hostArr) {
          if (hostArr.length === 0) {
            return "--";
          }
          const product_name = hostArr.map((item) => {
            if (item.name) {
              return `${item.product_name}-${item.name}-${this.status[item.status]
                }`;
            } else {
              return `${item.product_name}-${this.status[item.status]}`;
            }
          });
          return product_name.join("、");
        },
        handelRec(item) {
          this.recData = { ...item };
          this.isShowKd = true;
        },
        handelDetail(id) {
          location.href = `/plugin/${this.pluginId(
            "EContract"
          )}/contractDetail.htm?id=${id}`;
        },
        handelSign(id) {
          location.href = `/plugin/${this.pluginId(
            "EContract"
          )}/signContract.htm?id=${id}`;
        },
        handelCancel(id) {
          this.cancelId = id;
          this.isShowCancel = true;
        },
        handeInfo() {
          this.$refs.contractInfo.getContractInfo();
        },
        handelMail(id) {
          this.mailFormData.id = id;
          this.isShowMailDia = true;
        },
        MailClose() {
          this.isShowMailDia = false;
          this.$refs.mailForm.resetFields();
        },
        creditClose() {
          this.isShowCreditDia = false;
          this.params7.id = "";
          this.params7.page = 1;
          this.params7.limit = 20;
          this.dataList7 = [];
        },
        saveMailData() {
          this.$refs.mailForm.validate((valid) => {
            if (valid) {
              mailContract(this.mailFormData)
                .then((res) => {
                  this.MailClose();
                  this.$refs.payDialog.showPayDialog(res.data.data.data.id);
                })
                .catch((err) => {
                  this.$message.error(err.data.msg);
                });
            }
          });
        },
        saveCancel() {
          cancelContrat(this.cancelId)
            .then((res) => {
              this.cancelClose();
              this.$message.success(res.data.msg);
              this.getContractList();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        },
        cancelClose() {
          this.isShowCancel = false;
        },
      
        // 自动触发一次
        getRule(arr) {
          let isShow1 = this.showFun(arr, "OrderController::index");
          let isShow2 = this.showFun(arr, "TransactionController::list");
          let isShow3 = this.showFun(arr, "AccountController::creditList");
          if (this.isShowContract) {
            this.activeIndex = "5";
          }
          if (this.isShowCash) {
            this.activeIndex = "4";
          }
          // 余额记录
          if (isShow3) {
            this.activeIndex = "3";
            this.isShowBalance = true;
          }
          // 交易记录
          if (isShow2) {
            this.activeIndex = "2";
            this.isShowTransactionController = true;
          }
          // 订单记录
          if (isShow1) {
            this.activeIndex = "1";
            this.isShowOrderController = true;
          }
          try {
            const isActive = location.href.split("?")[1].split("=")[1] || "";
            if (sessionStorage.financeActiveIndex && isActive === "true") {
              this.activeIndex = sessionStorage.financeActiveIndex;
            }
          } catch { }
          // 首页跳转
          if (this.tab) {
            this.activeIndex = String(this.tab);
            this.tab === "4" && this.showVoucherDialog();
          }
          this.handleClick();
        },
        showFun(arr, str) {
          if (typeof arr == "string") {
            return true;
          } else {
            let isShow = "";
            isShow = arr.find((item) => {
              let isHave = item.includes(str);
              if (isHave) {
                return isHave;
              }
            });
            return isShow;
          }
        },
        // 去往提现列表
        goWithdrawal() {
          location.href = "/withdrawal.htm";
        },
        //获取订单列表
        getorderList() {
          this.loading1 = true;
          orderList(this.params1).then((res) => {
            if (res.data.status === 200) {
              this.params1.total = res.data.data.count;
              let list = res.data.data.list;
              list.map((item) => {
                let product_name = "";
                // 商品名称 含两个以上的 只取前两个拼接然后拼接上商品名称的个数
                if (item.product_names.length > 2) {
                  product_name =
                    item.product_names[0] +
                    "、" +
                    item.product_names[1] +
                    " " +
                    lang.finance_text121 +
                    item.product_names.length +
                    lang.finance_text122;
                } else {
                  item.product_names.map((n) => {
                    product_name += n + "、";
                  });
                  product_name = product_name.slice(0, -1);
                }
                item.product_name = product_name;

                // 判断有无子数据
                if (item.order_item_count > 1) {
                  // item.children = []
                  item.hasChildren = true;
                }
                item.data = [];
              });

              this.dataList1 = list;
            }
            this.loading1 = false;
          });
        },
        //获取出账周期订单列表
        getCreditorderList() {
          this.loading7 = true;
          creditOrderList(this.params7).then((res) => {
            if (res.data.status === 200) {
              this.params7.total = res.data.data.count;
              let list = res.data.data.list;
              list.map((item) => {
                let product_name = "";
                // 商品名称 含两个以上的 只取前两个拼接然后拼接上商品名称的个数
                if (item.product_names.length > 2) {
                  product_name =
                    item.product_names[0] +
                    "、" +
                    item.product_names[1] +
                    " " +
                    lang.finance_text121 +
                    item.product_names.length +
                    lang.finance_text122;
                } else {
                  item.product_names.map((n) => {
                    product_name += n + "、";
                  });
                  product_name = product_name.slice(0, -1);
                }
                item.product_name = product_name;

                // 判断有无子数据
                if (item.order_item_count > 1) {
                  // item.children = []
                  item.hasChildren = true;
                }
                item.data = [];
              });

              this.dataList7 = list;
            }
            this.loading7 = false;
          });
        },
        // 获取交易记录列表
        getTransactionList() {
          this.loading2 = true;
          transactionList(this.params2).then((res) => {
            if (res.data.status === 200) {
              let list = res.data.data.list;
              if (list) {
                list.map((item) => {
                  if (item.order_id == 0) {
                    item.order_id = "--";
                  }
                });
              }
              this.dataList2 = list;
              this.params2.total = res.data.data.count;
            }
            this.loading2 = false;
          });
        },
        // 获取余额记录列表
        getCreditList() {
          this.loading3 = true;
          if (this.date && this.date[0]) {
            this.params3.start_time = this.date[0] / 1000;
            this.params3.end_time = this.date[1] / 1000;
          } else {
            this.params3.start_time = "";
            this.params3.end_time = "";
          }
          creditList(this.params3).then((res) => {
            if (res.data.status === 200) {
              let list = res.data.data.list;
              // 过滤人工订单 不显示
              // list = list.filter((item) => {
              //   return item.type !== "Artificial";
              // });
              this.dataList3 = list;
              this.params3.total = res.data.data.count;
            }
            this.loading3 = false;
          });
        },
        // 获取订单详情
        getOrderDetailsList(id) {
          this.loading4 = true;
          orderDetails(id).then((res) => {
            if (res.data.status === 200) {
              let data = res.data.data.order;
              let item = data.items;
              let product_name = "";
              if (item) {
                item.map((n) => {
                  if (n.product_name) {
                    product_name += n.product_name + "、";
                  }
                });
              }
              data.product_name = product_name.slice(0, -1);
              let order = [];
              order.push(data);
              this.dataList4 = order;
            }
            this.loading4 = false;
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
        // tab点击事件
        handleClick() {
          sessionStorage.financeActiveIndex = this.activeIndex;
          if (this.activeIndex == 1) {
            // 订单记录
            this.getorderList();
          }
          if (this.activeIndex == 2) {
            // 交易记录
            this.getTransactionList();
          }
          if (this.activeIndex == 3) {
            // 余额记录
            this.getCreditList(3);
          }
          if (this.activeIndex == 4) {
            // 代金券
            this.getVoucher();
          }
          if (this.activeIndex == 5) {
            // 电子合同
            this.getContractList();
          }
          if (this.activeIndex == 6) {
            // 信用额
            this.getCreditDetail();
          }
          if (this.activeIndex == 7) {
            // 平台币
            this.initCoinPage(true);
          }
        },
        changeCoinType() {
          this.coinParams.page = 1;
          this.initCoinPage();
        },
        initCoinPage(init = false) {
          switch (this.coinType) {
            case "wait":
              this.getCoinWaitList();
              break;
            default:
              init && this.getCoinWaitList(true);
              this.getCoinList();
              break;
          }
        },

        getCoinList() {
          this.coinLoading = true;
          apiCoinList({
            status: this.coinType,
            page: this.coinParams.page,
            limit: this.coinParams.limit,
          })
            .then((res) => {
              this.coinList = res.data.data.list.map((item) => {
                return {
                  ...item,
                  isShow: false,
                  get_loading: false,
                  is_loading: false,
                  use_detail_list: [],
                };
              });
              this.coinParams.total = res.data.data.count;
              this.coinLoading = false;
            })
            .catch(() => {
              this.coinLoading = false;
            });
        },

        // 待领取平台币列表
        getCoinWaitList(isGetTotal = false) {
          !isGetTotal && (this.coinLoading = true);
          apiCoinWaitList(this.coinParams)
            .then((res) => {
              if (isGetTotal === true) {
                this.waitCoinTotal = res.data.data.count;
                return;
              }
              this.coinParams.total = res.data.data.count;
              this.waitCoinTotal = res.data.data.count;
              this.coinList = res.data.data.list.map((item) => {
                return {
                  ...item,
                  isShow: false,
                  get_loading: false,
                  is_loading: false,
                  use_detail_list: [],
                };
              });
              this.coinLoading = false;
            })
            .catch(() => {
              this.coinLoading = false;
            });
        },
        // 合同列表
        getContractList() {
          this.loading5 = true;
          contractList(this.params4).then((res) => {
            this.loading5 = false;
            if (res.data.status === 200) {
              this.dataList5 = res.data.data.list;
              this.params4.total = res.data.data.count;
            }
          });
        },
        // 信用额列表
        getCredittList() {
          this.loading6 = true;
          creditLimtList(this.params6).then((res) => {
            this.loading6 = false;
            if (res.data.status === 200) {
              this.dataList6 = res.data.data.list;
              this.params6.total = res.data.data.count;
            }
          });
        },
        //  授信详情
        getCreditDetail() {
          this.getCredittList();
          creditDetail().then((res) => {
            if (res.data.status === 200) {
              this.creditData = res.data.data.credit_limit;
            }
          });
        },
        handelCredit(id) {
          this.params7.id = id;
          this.getCreditorderList();
          this.isShowCreditDia = true;
        },
        // pgae
        sizeChange1(e) {
          this.params1.limit = e;
          this.getorderList();
        },
        sizeChange2(e) {
          this.params2.limit = e;
          this.getTransactionList();
        },
        sizeChange3(e) {
          this.params3.limit = e;
          this.getCreditList();
        },
        sizeChange4(e) {
          this.params4.limit = e;
          this.getContractList();
        },
        sizeChange6(e) {
          this.params6.limit = e;
          this.getCredittList();
        },
        sizeChange7(e) {
          this.params7.limit = e;
          this.getCreditorderList();
        },
        currentChange1(e) {
          this.params1.page = e;
          this.getorderList();
        },
        currentChange7(e) {
          this.params7.page = e;
          this.getCreditorderList();
        },
        currentChange2(e) {
          this.params2.page = e;
          this.getTransactionList();
        },
        currentChange3(e) {
          this.params3.page = e;
          this.getCreditList();
        },
        currentChange4(e) {
          this.params4.page = e;
          this.getContractList();
        },
        currentChange6(e) {
          this.params6.page = e;
          this.getCredittList();
        },
        // 订单记录 搜索框事件
        inputChange1() {
          this.params1.page = 1;
          this.getorderList();
        },
        // 交易记录 搜索框事件
        inputChange2() {
          this.params2.page = 1;
          this.getTransactionList();
        },
        // 余额记录 搜索框事件
        inputChange3() {
          this.params3.page = 1;
          this.getCreditList();
        },
        inputChange4() {
          this.params4.page = 1;
          this.getContractList();
        },
        changeDate() { },
        // 获取通用配置
        getCommon() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          document.title =
            this.commonData.website_name + "-" + lang.finance_text123;
          this.currency_prefix = this.commonData.currency_prefix;
          this.currency_code = this.commonData.currency_code;
        },
        // 获取账户详情
        getAccount() {
          account().then((res) => {
            if (res.data.status === 200) {
              this.accountData = res.data.data.account;
              this.balance = res.data.data.account.credit;
              this.freeze_credit = res.data.data.account.freeze_credit;
            }
          });
        },
        getUnAmount() {
          havePlugin("IdcsmartRefund") &&
            unAmount().then((res) => {
              if (res.data.status === 200) {
                this.unAmount = res.data.data.amount;
              }
            });
        },
        // 显示充值 dialog
        showCz() {
          this.$refs.rechargeDialog.open();
        },
        rechargeSuccess() {
          this.getAccount();
        },
        keepTwoDecimalFull(num) {
          let result = parseFloat(num);
          if (isNaN(result)) {
            return num;
          }
          result = Math.round(num * 100) / 100;
          let s_x = result.toString(); //将数字转换为字符串

          let pos_decimal = s_x.indexOf("."); //小数点的索引值

          // 当整数时，pos_decimal=-1 自动补0
          if (pos_decimal < 0) {
            pos_decimal = s_x.length;
            s_x += ".";
          }

          // 当数字的长度< 小数点索引+2时，补0
          while (s_x.length <= pos_decimal + 2) {
            s_x += "0";
          }
          return s_x;
        },
        // 显示提现 dialog
        showTx() {
          this.getWithdrawRule();
        },
        dowithdraw(params) {
          // 推介计划提现
          withdraw(params)
            .then((res) => {
              if (res.data.status == 200) {
                this.$message.success(lang.finance_text124);
                // 关闭提现弹窗
                this.getAccount();
                this.$refs.withdrawDialog.withdrawCancel();
                this.isdot = true;
              }
            })
            .catch((error) => {
              this.$message.error(error.data.msg);
              // 关闭提现提交按钮的加载状态
              this.$refs.withdrawDialog.withdrawLoading = false;
            });
        },

        // 快递信息弹窗关闭
        kdClose() {
          this.isShowKd = false;
        },

        getRowKey(row) {
          return row.id + "-" + row.host_id;
        },
        // 订单记录订单详情
        load(tree, treeNode, resolve) {
          // 获取订单详情
          const id = tree.id;
          orderDetails(id).then((res) => {
            if (res.data.status === 200) {
              let resData = res.data.data.order.items;
              resolve(resData);
            }
          });
          // this.getOrderDetailsList(id)
        },
        // 打开删除弹窗
        openDeletaDialog(row, index) {
          this.isBatchDel = false;
          this.isShowDeOrder = true;
          this.deleteObj = {
            id: row.id,
            index: index,
          };
        },
        suerDelOrder() {
          if (this.isBatchDel) {
            this.handelBatchDel();
          } else {
            this.handelDeleteOrder();
          }
        },
        // 批量删除订单
        handelBatchDel() {
          this.oderDelLoading = true;
          batchDeleteOrder({ id: this.multipleSelection })
            .then((res) => {
              this.multipleSelection = [];
              this.$refs.table1.clearSelection();
              this.isShowDeOrder = false;
              this.$message.success(res.data.msg);
              this.inputChange1();
            })
            .catch((error) => {
              this.$message.error(error.data.msg);
            })
            .finally(() => {
              this.oderDelLoading = false;
            });
        },
        // 删除订单
        handelDeleteOrder() {
          this.oderDelLoading = true;
          delete_order(this.deleteObj.id)
            .then((res) => {
              this.dataList1.splice(this.deleteObj.index, 1);
              this.params1.total = Number(this.params1.total) - 1;
              this.$message.success(res.data.msg);
              this.isShowDeOrder = false;
            })
            .catch((error) => {
              this.$message.error(error.data.msg);
              this.isShowDeOrder = false;
            })
            .finally(() => {
              this.oderDelLoading = false;
            });
        },
        // 交易记录订单详情
        rowClick(orderId) {
          this.isDetail = true;
          // const id = row.order_id
          this.getOrderDetailsList(orderId);
        },
        // 去订单详情
        goOrderDetail(id) {
          location.href = `orderDetail.htm?id=${id}`;
        },
        // 支付弹窗相关
        // 点击去支付
        showPayDialog(row) {
          const orderId = row.id;
          const amount = row.amount;
          const isRecharge = row.type === "recharge";
          this.$refs.payDialog.showPayDialog(orderId, amount, null, isRecharge);
        },
        // 支付成功回调
        paySuccess(e) {
          this.useCredit = true;
          this.getAccount();
          this.getUnAmount();
          this.handleClick();
        },
        // 取消支付回调
        payCancel(e) {
          this.useCredit = true;
          this.handleClick();
        },

        handleSelectionChange(val) {
          this.multipleSelection = val.map((item) => {
            return item.id;
          });
        },
        handelAllPay() {
          if (this.multipleSelection.length === 0) {
            this.$message.warning(lang.finance_text134);
            return;
          }
          this.allLoading = true;
          combineOrder({ ids: this.multipleSelection })
            .then((res) => {
              this.allLoading = false;
              const orderId = res.data.data.id;
              this.$refs.payDialog.showPayDialog(orderId);
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
              this.allLoading = false;
            });
        },
        handelAllDel() {
          if (this.multipleSelection.length === 0) {
            this.$message.warning(lang.finance_text147);
            return;
          }
          this.isBatchDel = true;
          this.isShowDeOrder = true;
        },

        handelExport() {
          this.exportLoading = true;
          apiExportOrder(this.params1)
            .then((res) => {
              exportExcelFun(res).finally(() => {
                this.exportLoading = false;
              });
            })
            .catch((err) => {
              this.exportLoading = false;
              this.$message.error(err.data.msg);
            });
        },

        // 获取提现规则
        getWithdrawRule() {
          withdrawRule().then((res) => {
            if (res.data.status === 200) {
              this.ruleData = res.data.data;
              const ruler = {
                // 提现来源
                source: "credit",
                // 提现方式
                method: [],
                // 第一个提现方式
                method_id: "",
                // 可提现金额
                withdrawable_amount: this.canUseBalance,
                // 单次提现最提金额
                withdraw_min: "",
                // 单次提现最高金额
                withdraw_max: "",
                // 提现手续费 百分比的带上“%” 固定金额 保留两位数
                withdraw_handling_fee: "",
                // 最低提现手续费
                percent_min: "",
              };
              ruler.method = this.ruleData.method;
              ruler.method_id = this.ruleData.method[0]
                ? this.ruleData.method[0].id
                : "";
              ruler.withdraw_max = this.keepTwoDecimalFull(this.ruleData.max);
              ruler.withdraw_min = this.keepTwoDecimalFull(this.ruleData.min);
              // 如果是固定的费用就取 withdraw_fee
              if (this.ruleData.withdraw_fee_type == "fixed") {
                ruler.withdraw_handling_fee =
                  this.currency_prefix + this.ruleData.withdraw_fee;
              } else {
                // 如果是百分比收费就取 percent 不过加上 符号
                ruler.withdraw_handling_fee = this.ruleData.percent
                  ? this.ruleData.percent + "%"
                  : "";
              }
              ruler.percent_min = this.keepTwoDecimalFull(
                this.ruleData.percent_min
              );
              this.$refs.withdrawDialog.shwoWithdrawal(ruler);
            }
          });
        },

        // 返回顶部
        goBackTop() {
          document.documentElement.scrollTop = document.body.scrollTop = 0;
        },
        // 移动端item展开
        showItem(item) {
          if (item.hasChildren) {
            const id = item.id;
            orderDetails(id).then((res) => {
              if (res.data.status === 200) {
                let resData = res.data.data.order.items;
                this.dataList1.map((n) => {
                  if (n.id === item.id) {
                    // n.id = 1
                    n.data = resData;
                  }
                });
              }
            });
          }
        },

        // 代金卷
        showVoucherDialog() {
          this.dialogVisible = true;
          this.getVoucherAvailable();
        },
        toggleVoucher(row) {
          this.voucherList
            .filter((item) => item.id !== row.id)
            .map((item) => {
              item.isShow = false;
              return item;
            });
          this.voucherAvailableList
            .filter((item) => item.id !== row.id)
            .map((item) => {
              item.isShow = false;
              return item;
            });
          row.isShow = !row.isShow;
        },

        toggleCoin(row) {
          this.coinList
            .filter((item) => item.id !== row.id)
            .map((item) => {
              item.isShow = false;
              return item;
            });
          row.isShow = !row.isShow;
        },
        // 可领代金券
        async getVoucherAvailable() {
          try {
            this.diaLoading = true;
            const res = await voucherAvailable(this.availableParams);
            let temp = res.data.data.list;
            temp = temp.map((item) => {
              item.isShow = false;
              return item;
            });
            this.voucherAvailableList = temp;
            this.availableParams.total = res.data.data.count;
            this.diaLoading = false;
          } catch (error) {
            this.diaLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 点击领取
        async sureGet(item) {
          try {
            if (item.is_get) {
              return;
            }
            const res = await voucherGet({ id: item.id });
            this.$message.success(res.data.msg);
            this.getVoucherAvailable();
            this.getVoucher();
          } catch (error) {
            this.$message.error(error.data.msg);
            this.getVoucherAvailable();
          }
        },
        // 获取我的代金券
        async getVoucher() {
          try {
            this.voucherLoading = true;
            const res = await voucherMine(this.vParams);
            let temp = res.data.data.list;
            temp = temp.map((item) => {
              item.isShow = false;
              return item;
            });
            this.voucherList = temp;
            this.vParams.total = res.data.data.count;
            this.voucherLoading = false;
          } catch (error) {
            this.voucherLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        sizeChangeCoin(e) {
          this.coinParams.limit = e;
          this.coinParams.page = 1;
          this.initCoinPage();
        },
        currentChangeCoin(e) {
          this.coinParams.page = e;
          this.initCoinPage();
        },

        // 每页展示数改变
        sizeChange(e) {
          this.vParams.limit = e;
          this.vParams.page = 1;
          // 获取列表
          this.getVoucher();
        },
        // 当前页改变
        currentChange(e) {
          this.vParams.page = e;
          this.getVoucher();
        },
      },
    }).$mount(finance);
    typeof old_onload == "function" && old_onload();
  };
})(window);

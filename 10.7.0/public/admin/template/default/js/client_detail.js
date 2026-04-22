(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("client-detail")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}`;
    const adminOperateVue = new Vue({
      components: {
        comConfig,
        comChooseUser,
        safeConfirm,
        comPagination,
        comViewimg
      },
      data() {
        return {
          baseUrl: str,
          id: "", // 用户id
          data: [],
          currency_prefix:
            JSON.parse(localStorage.getItem("common_set")).currency_prefix ||
            "¥",
          currency_suffix: JSON.parse(localStorage.getItem("common_set"))
            .currency_suffix,
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          hover: true,
          diaTitle: "",
          clientCustomList: [],
          logColumns: [
            {
              colKey: "ip",
              title: `IP${lang.address}`,
            },
            {
              colKey: "login_time",
              title: lang.login_time,
            },
          ],
          total: 0,
          pageSizeOptions: [20, 50, 100],
          loading: false,
          moneyLoading: false,
          statusVisble: false,
          oauth: [],
          mp_weixin_notice: 0,
          title: "",
          delId: "",
          formData: {
            id: "",
            username: "",
            level_id: "",
            phone_code: "",
            phone: "",
            email: "",
            country_id: "",
            address: "",
            company: "",
            language: "zh-cn",
            notes: "",
            password: "",
            operate_password: "",
            customfield: {},
            receive_sms: 0,
            receive_email: 0,
          },
          receiveLoading: false,
          admin_operate_password: "",
          clientList: [], // 用户列表
          rules: {
            // country: [
            //   {
            //     validator: (val) => val && val.length <= 100,
            //     message: `${lang.verify3}100`,
            //     type: "waring",
            //   },
            // ],
            // address: [
            //   {
            //     validator: (val) => val && val.length <= 255,
            //     message: `${lang.verify3}255`,
            //     type: "waring",
            //   },
            // ],
            // notes: [
            //   {
            //     validator: (val) => val && val.length <= 10000,
            //     message: `${lang.verify3}10000`,
            //     type: "waring",
            //   },
            // ],
            password: [
              {
                pattern: /^[\w@!#$%^&*()+-_]{6,32}$/,
                message: lang.verify8 + "6~32" + "，" + lang.verify14,
                type: "warning",
              },
            ],
          },
          visibleMoney: false,
          visibleLog: false,
          moneyData: {
            // 充值/扣费
            id: "",
            type: "", //  recharge充值 deduction扣费
            amount: "",
            notes: "",
          },
          moneyRules: {
            type: [
              {
                required: true,
                message: lang.select + lang.type,
                type: "error",
              },
            ],
            amount: [
              {
                required: true,
                message: lang.input + lang.money,
                type: "error",
              },
              {
                pattern: /^\d+(\.\d{0,2})?$/,
                message: lang.verify5,
                type: "warning",
              },
              {
                validator: (val) => val > 0,
                message: lang.verify5,
                type: "warning",
              },
            ],
            notes: [
              {
                required: true,
                message: lang.input + lang.content,
                type: "error",
              },
            ],
          },
          range: [],
          presets: {
            [lang.due_today]: [
              new Date(new Date().toLocaleDateString()).getTime(),
              new Date(new Date().toLocaleDateString()).getTime() +
              24 * 60 * 60 * 1000 -
              1,
            ],
            [lang.due_three]: [
              new Date(+new Date() - 86400000 * 2),
              new Date(),
            ],
            [lang.due_seven]: [
              new Date(+new Date() - 86400000 * 6),
              new Date(),
            ],
            [lang.due_month]: [
              new Date(+new Date() - 86400000 * 30),
              new Date(),
            ],
          },
          logCunt: 0,
          // 变更记录
          logData: [],
          page_total_amount: 0.0,
          total_amount: 0.0,
          columns: [
            {
              colKey: "id",
              title: "ID",
              width: 120,
            },
            {
              colKey: "amount",
              title: lang.change_money,
              width: 120,
            },
            {
              colKey: "type",
              title: lang.type,
              width: 120,
            },
            {
              colKey: "create_time",
              title: lang.change_time,
              width: 180,
            },
            {
              colKey: "credit",
              title: lang.setting_text147,
              width: 120,
            },

            {
              colKey: "notes",
              title: lang.notes,
              ellipsis: true,
              width: 200,
            },
            {
              colKey: "admin_name",
              title: lang.operator,
              width: 100,
            },
          ],
          params: {
            keywords: "",
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          moneyPage: {
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
            type: "",
            keywords: "",
            start_time: "",
            end_time: "",
          },
          logSizeOptions: [20, 50, 100],
          statusTip: "",
          country: [],
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
          clientTotal: 0,
          clinetParams: {
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          website_url: "",
          refundAmount: 0.0,
          authList: JSON.parse(
            JSON.stringify(localStorage.getItem("backAuth"))
          ),
          // 充值相关开始
          // 是否显示充值弹窗
          visibleRecharge: false,
          hasClientCustom: false,
          hasCertification: false,
          hasMpWeixinNotice: false,
          // 充值弹窗数据
          rechargeData: {
            gateway: "",
            amount: "",
            transaction_number: "",
            notes: "",
          },
          idcsmart_sale_id: "",
          idcsmart_sale_list: [],
          hasIdcsmart_sale: false,
          clientCoinData: {
            leave_amount: "",
            name: "",
            list: "",
          },
          certificationDetail: {},
          certificationVisible: false,
          // 充值弹窗提交验证
          rechargeRules: {
            amount: [
              {
                required: true,
                message: lang.input + lang.money,
                type: "error",
              },
              {
                pattern: /^\d+(\.\d{0,2})?$/,
                message: lang.verify5,
                type: "warning",
              },
              {
                validator: (val) => val > 0,
                message: lang.verify5,
                type: "warning",
              },
            ],
          },
          // 支付方式列表
          gatewayList: [],
          // 充值相关结束
          levelList: [],
          submitLoading: false,
          hasPlugin: false,
          childList: [],
          childParams: {
            page: 1,
            limit: getGlobalLimit(),
          },
          childTotal: 0,
          childColumns: [
            {
              colKey: "id",
              align: "center",
              title: "ID",
              width: 140,
            },
            {
              colKey: "username",
              align: "center",
              title: lang.user_text17,
            },
            {
              colKey: "last_action_time",
              align: "center",
              title: lang.user_text19,
              width: 210,
            },
            {
              title: lang.operation,
              colKey: "caozuo",
              align: "center",
              width: 120,
              fixed: "right",
            },
          ],
          refundTip: "",
          hasTicket: false, // 是否安装工单
          searchLoading: false,
          hasNewTicket: false,
          hasRecommend: false,
          devloperType: "",
          creditChangeType: {
            Recommend: lang.data_export_tip9,
            Artificial: lang.user_text28,
            Recharge: lang.user_text29,
            Applied: lang.user_text30,
            Overpayment: lang.user_text31,
            Underpayment: lang.user_text32,
            Refund: lang.user_text33,
            Freeze: lang.user_text34,
            Unfreeze: lang.user_text35,
            Withdraw: lang.user_text8
          },
          freezeList: [],
          freezeLoading: false,
          freezeVisible: false,
          freezeColumns: [
            {
              colKey: "id",
              title: "ID",
              width: 120,
            },
            {
              colKey: "amount",
              title: lang.user_text26,
              width: 120,
              align: "right",
            },

            {
              colKey: "create_time",
              title: lang.user_text27,
              width: 180,
            },
            {
              colKey: "notes",
              title: lang.tem_notes,
              ellipsis: true,
              width: 200,
            },
          ],
          freezeRules: {
            freeze_amount: [
              {
                required: true,
                message: lang.input + lang.user_text36,
                type: "error",
              },
            ],
            client_notes: [
              {
                required: true,
                message: lang.input + lang.user_text37,
                type: "error",
              },
            ],
          },
          freezeForm: {
            freeze_amount: "",
            client_notes: "",
            notes: "",
          },
          unfreezeVisible: false,
          unfreezeForm: {
            credit_ids: [],
            notes: "",
          },
          freezePopVisible: false,
          isEditFree: false,
          hasCoin: false,
          preImg: "",
        };
      },
      watch: {
        freezePopVisible(val) {
          if (!val && this.isEditFree) {
            this.freezePopVisible = true;
          }
        },
      },
      computed: {
        calcDevloper() {
          switch (this.devloperType) {
            case 1:
              return `（${lang.author}）`;
            case 2:
              return `（${lang.client_service}）`;
            case 3:
              return `（${lang.author_service}）`;
            default:
              return "";
          }
        },
        inputLabel() {
          if (this.moneyData.type === "recharge") {
            return this.currency_prefix;
          } else {
            return "-" + this.currency_prefix;
          }
        },
        calcRefund() {
          this.refundTip = `${lang.refund_to_balance}：${this.refundAmount}\n${lang.refund_to_user
            }：${this.data.refund?.replace("-", "")}`;
          return (this.refundAmount * 1 - this.data.refund * 1 || 0).toFixed(2);
        },
        calcShow() {
          return (data) => {
            return (
              `#${data.id}-` +
              (data.username
                ? data.username
                : data.phone
                  ? data.phone
                  : data.email) +
              (data.company ? `(${data.company})` : "")
            );
          };
        },
        isExist() {
          return !this.clientList.find((item) => item.id === this.data.id);
        },
        unfreezeColumns() {
          const columns = JSON.parse(JSON.stringify(this.freezeColumns));
          columns.unshift({
            colKey: "row-select",
            type: "multiple",
            width: 50,
          });
          columns.push({
            colKey: "nickname",
            title: lang.user_text43,
            width: 140,
            fixed: "right",
          });
          return columns;
        },
      },
      created() {
        const query = location.href.split("?")[1].split("&");
        this.moneyData.id = this.id = Number(this.getQuery(query[0]));
        // 所有之前跳转用户详情都是 client_detail
        if (
          !this.$checkPermission("auth_user_detail_personal_information_view")
        ) {
          const clientAuth = [
            { auth: "auth_user_detail_host_info_view", url: "client_host" },
            { auth: "auth_user_detail_order_view", url: "client_order" },
            {
              auth: "auth_user_detail_transaction_view",
              url: "client_transaction",
            },
            { auth: "auth_user_detail_operation_log", url: "client_log" },
            {
              auth: "auth_user_detail_notification_log_sms_notification",
              url: "client_notice_sms",
            },
            {
              auth: "auth_user_detail_notification_log_email_notification",
              url: "client_notice_email",
            },
            { auth: "auth_user_detail_ticket_view", url: "client_ticket" },
            {
              auth: "auth_user_detail_info_record_view",
              url: "client_records",
            },
          ];
          const firstItem = clientAuth.find((item) =>
            this.$checkPermission(item.auth)
          );
          if (firstItem.auth === "auth_user_detail_ticket_view") {
            return (location.href = `${this.baseUrl}/plugin/idcsmart_ticket/${firstItem.url}.htm?id=${this.id}`);
          } else {
            return (location.href = `${this.baseUrl}/${firstItem.url}.htm?id=${this.id}`);
          }
        }

        this.langList = JSON.parse(
          localStorage.getItem("common_set")
        ).lang_home;
        this.getUserDetail();
        this.getCountry();
        // 获取用户列表
        // this.getClintList();
        this.getSystemOption();
        // 获取支付方式列表
        this.getGatewayList();
        // 获取退款
        // this.getRefundAmount();
        this.getPlugin();
        // 子账户列表
        // this.getchildAccountList();
        document.title =
          lang.user_list +
          "-" +
          lang.personal +
          "-" +
          localStorage.getItem("back_website_name");
        this.getFreezeList();
      },
      methods: {
        rehandleSelectChange(value) {
          this.unfreezeForm.credit_ids = value;
        },
        hanelFreeze() {
          this.freezeVisible = true;
        },
        hanelUnfreeze() {
          this.unfreezeVisible = true;
        },
        handleVisibleChange(val) {
          if (!val && !this.isEditFree) {
            this.unfreezeClose();
          }
        },
        unfreezeClose() {
          this.freezePopVisible = false;
          this.unfreezeForm.credit_ids = [];
          this.$refs.unfreezeRef && this.$refs.unfreezeRef.reset();
        },
        freezeClose() {
          this.freezeVisible = false;
          this.$refs.freezeRef && this.$refs.freezeRef.reset();
        },
        confirmUnFreeze() {
          if (this.unfreezeForm.credit_ids.length === 0) {
            this.$message.warning(lang.user_text44);
            return;
          }
          // if (!this.unfreezeForm.notes) {
          //   this.$message.warning(lang.user_text46);
          //   return;
          // }
          // 调用充值接口
          const params = {
            client_id: this.id,
            ...this.unfreezeForm,
          };
          this.submitLoading = true;
          apiUnFreezeCredit(params)
            .then((res) => {
              if (res.data.status === 200) {
                this.$message.success(res.data.msg);
                // 关闭弹窗
                this.unfreezeClose();
                // 刷新余额
                this.getUserDetail();
                this.getFreezeList();
              }
            })
            .catch((error) => {
              this.$message.error(error.data.msg);
            })
            .finally(() => {
              this.submitLoading = false;
            });
        },

        confirmFreeze({ validateResult, firstError }) {
          if (validateResult === true) {
            // 调用充值接口
            const params = {
              client_id: this.id,
              ...this.freezeForm,
            };
            if (!params.notes) {
              params.notes = params.client_notes;
            }

            this.submitLoading = true;
            apiFreezeCredit(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(res.data.msg);
                  // 关闭弹窗
                  this.freezeClose();
                  // 刷新余额
                  this.getUserDetail();
                  this.getFreezeList();
                }
              })
              .catch((error) => {
                this.$message.error(error.data.msg);
              })
              .finally(() => {
                this.submitLoading = false;
              });
          } else {
          }
        },
        lookImg(url) {
          this.$refs.viewimgRef.showViewer(url);
        },
        handelCertification(id) {
          this.getCertificationDetail(id);
        },
        // 获取详情
        async getCertificationDetail(id) {
          try {
            const res = await getRealNameDetail(id);
            const temp = { ...res.data.data.log };
            temp.fontUrl = temp.img[0];
            temp.backUrl = temp.img[1];
            temp.slicense = temp.img[2];
            // 以前老的数据的企业认证只有一张图片，这里兼容一下
            if (temp.type === 2 || temp.type === 3) {
              if (!temp.img[2]) {
                temp.slicense = temp.img[0];
                temp.fontUrl = undefined;
                temp.backUrl = undefined;
              }
            }
            this.certificationDetail = temp;
            this.certificationVisible = true;
          } catch (error) {
            console.log(error);
          }
        },

        closeCertification() {
          this.certificationVisible = false;
        },
        getFreezeList() {
          this.freezeLoading = true;
          apiFreezeRecord({ client_id: this.id })
            .then((res) => {
              this.freezeList = res.data.data.list;
              this.freezeLoading = false;
            })
            .catch((error) => {
              this.freezeLoading = false;
            });
        },
        async handleChangeReceive(bol, type) {
          try {
            if (this.receiveLoading) {
              return;
            }
            this.receiveLoading = true;
            const res = await updateReceive(type, {
              id: this.id,
              [type]: bol,
            });
            this.$message.success(res.data.msg);
            this.receiveLoading = false;
            this.getUserDetail();
          } catch (error) {
            this.$message.error(error.data.msg);
            this.receiveLoading = false;
          }
        },
        thousandth(num) {
          if (!num) {
            num = 0.0;
          }
          let str = num.toString(); // 数字转字符串
          let str2 = null;
          // 如果带小数点
          if (str.indexOf(".") !== -1) {
            // 带小数点只需要处理小数点左边的
            const strArr = str.split("."); // 根据小数点切割字符串
            str = strArr[0]; // 小数点左边
            str2 = strArr[1]; // 小数点右边
            //如12345.678  str=12345，str2=678
          }
          let result = ""; // 结果
          while (str.length > 3) {
            // while循环 字符串长度大于3就得添加千分位
            // 切割法 ，从后往前切割字符串 ⬇️
            result = "," + str.slice(str.length - 3, str.length) + result;
            // 切割str最后三位，用逗号拼接 比如12345 切割为 ,345
            // 用result接收，并拼接上一次循环得到的result
            str = str.slice(0, str.length - 3); // str字符串剥离上面切割的后三位，比如 12345 剥离成 12
          }

          if (str.length <= 3 && str.length > 0) {
            // 长度小于等于3 且长度大于0，直接拼接到result
            // 为什么可以等于3 因为上面result 拼接时候在前面带上了‘,’
            // 相当于123456 上一步处理完之后 result=',456' str='123'
            result = str + result;
          }
          // 最后判断是否带小数点（str2是小数点右边的数字）
          // 如果带了小数点就拼接小数点右边的str2 ⬇️
          str2 ? (result = result + "." + str2) : "";
          return result;
        },
        // 远程搜素
        remoteMethod(key) {
          this.clinetParams.keywords = key;
          this.getClintList();
        },
        filterMethod(search, option) {
          return option;
        },
        async getPlugin() {
          try {
            /* IdcsmartClientLevel */
            const res = await getAddon();
            const temp = res.data.data.list.reduce((all, cur) => {
              all.push(cur.name);
              return all;
            }, []);
            this.hasPlugin = temp.includes("IdcsmartClientLevel");
            this.hasTicket = temp.includes("IdcsmartTicket");
            this.hasNewTicket = temp.includes("TicketPremium");
            this.hasRecommend = temp.includes("IdcsmartRecommend");
            this.hasClientCustom = temp.includes("ClientCustomField");
            this.hasCertification = temp.includes("IdcsmartCertification");
            this.hasMpWeixinNotice = temp.includes("MpWeixinNotice");
            this.hasCoin = temp.includes("Coin");

            temp.includes("Coin") && this.getClientCoin();
            temp.includes("IdcsmartSubAccount") && this.getchildAccountList();
            temp.includes("IdcsmartRefund") && this.getRefundAmount();
            temp.includes("ClientCustomField") && this.getClientCustomField();
            // 安装了插件才执行
          } catch (error) { }
        },
        calcRules(item) {
          const rules = [];
          if (item.required === 1) {
            rules.push({
              required: true,
              message: lang.custom_tip_text2,
              trigger: "blur",
            });
          } else {
            rules.push({
              required: false,
            });
          }
          if (item.type === "link") {
            // 类型为链接时需要校验url格式 http://www.baidu.com
            rules.push({
              url: {
                protocols: ["http", "https", "ftp"],
                require_protocol: true,
              },
            });
          }
          if (
            item.type !== "dropdown" &&
            item.type !== "tickbox" &&
            item.regexpr
          ) {
            rules.push({
              pattern: new RegExp(item.regexpr.replace(/^\/|\/$/g, "")),
              message: lang.custom_tip_text1,
            });
          }
          return rules;
        },
        getClientCustomField() {
          clientCustomDetail(this.id).then((res) => {
            this.clientCustomList = res.data.data.list.map((item) => {
              if (item.type === "tickbox") {
                item.value = item.value === "1";
              }
              if (item.type === "dropdown_text") {
                item.select_select = item.value.split("|")[0] || "";
                item.select_text = item.value.split("|")[1] || "";
                item.select_select = item.select_select || item.options[0];
              }
              return item;
            });
          });
        },
        // 获取退款
        async getRefundAmount() {
          try {
            const res = await getRefund(this.id);
            this.refundAmount = res.data.data.amount;
          } catch (error) { }
        },
        // 获取后台配置的路径
        async getSystemOption() {
          try {
            const res = await getSystemOpt();
            this.website_url =
              res.data.data.clientarea_url || res.data.data.website_url;
          } catch (error) { }
        },
        // 以用户登录
        async loginByUser() {
          try {
            const opener = window.open("", "_blank");
            const res = await loginByUserId(this.id);
            localStorage.setItem("jwt", res.data.data.jwt);
            localStorage.setItem("boxJwt", res.data.data.jwt);
            const url = `${this.website_url}/home.htm?queryParam=${res.data.data.jwt}`;
            opener.location = url;
          } catch (error) {
            console.log(error);
            this.$message.error(error.data.msg);
          }
        },
        changeUser(id) {
          this.id = id;
          location.href = `client_detail.htm?id=${this.id}`;
        },
        async getClintList() {
          try {
            this.searchLoading = true;
            const res = await getClientList(this.clinetParams);
            this.clientList = res.data.data.list;
            this.clientTotal = res.data.data.count;
            // if (this.clientList.length < this.clientTotal) {
            //   this.clinetParams.limit = this.clientTotal;
            //   this.getClintList();
            // }
            this.searchLoading = false;
          } catch (error) {
            this.searchLoading = false;
            console.log(error.data.msg);
          }
        },
        getQuery(val) {
          return val.split("=")[1];
        },
        // 删除用户
        deleteUser() {
          this.delVisible = true;
        },
        hadelSafeConfirm(val, remember) {
          this[val]("", remember);
        },
        async sureDelUser(e, remember_operate_password = 0) {
          // if (!this.admin_operate_password) {
          //   this.$refs.safeRef.openDialog("sureDelUser");
          //   return;
          // }
          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            this.submitLoading = true;
            const res = await deleteClient({
              id: this.id,
              admin_operate_password,
              admin_operate_methods: "sureDelUser",
              remember_operate_password,
            });
            this.delVisible = false;
            this.$message.success(res.data.msg);
            setTimeout(() => {
              this.submitLoading = false;
              location.href = "client.htm";
            }, 300);
          } catch (error) {
            this.submitLoading = false;
            if (error.data.data) {
              if (
                !admin_operate_password &&
                error.data.data.operate_password === 1
              ) {
                return;
              }
            }
            this.$message.error(error.data.msg);
          }
        },
        // 获取平台币
        async getClientCoin() {
          try {
            const res = await apiClientCoin({ client_id: this.id });
            this.clientCoinData = res.data.data;
          } catch (error) {
            console.log(error);
          }
        },
        // 启用/停用
        changeStatus() {
          this.statusVisble = true;
          this.statusTip = this.data.status ? lang.sure_Close : lang.sure_Open;
        },
        async sureChange(e, remember_operate_password = 0) {
          // if (!this.admin_operate_password) {
          //   this.$refs.safeRef.openDialog("sureChange");
          //   return;
          // }
          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            const params = {
              status: this.data.status === 1 ? 0 : 1,
              admin_operate_password,
              admin_operate_methods: "sureChange",
              remember_operate_password,
            };
            this.submitLoading = true;
            const res = await changeOpen(this.id, params);
            this.statusVisble = false;
            this.$message.success(res.data.msg);
            this.getUserDetail();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            if (error.data.data) {
              if (
                !admin_operate_password &&
                error.data.data.operate_password === 1
              ) {
                return;
              }
            }
            this.$message.error(error.data.msg);
          }
        },
        // 充值/扣费
        changeMoney() {
          this.moneyData.type = "recharge";
          this.moneyData.amount = "";
          this.moneyData.notes = "";
          this.visibleMoney = true;
        },
        // 充值相关开始
        // 显示充值弹窗
        showRecharge() {
          // 初始化充值数据
          this.rechargeData.gateway = this.gatewayList[0].name;
          this.rechargeData.amount = "";
          this.rechargeData.transaction_number = "";
          this.rechargeData.notes = "";
          this.visibleRecharge = true;
        },
        // 取消充值
        closeRechorge() {
          this.visibleRecharge = false;
        },
        // 充值提交
        confirmRecharge({ validateResult, firstError }) {
          if (validateResult === true) {
            // 调用充值接口
            const params = {
              client_id: this.id,
              amount: Number(this.rechargeData.amount),
              gateway: this.rechargeData.gateway,
              transaction_number: this.rechargeData.transaction_number,
              notes: this.rechargeData.notes,
            };
            this.submitLoading = true;
            recharge(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(res.data.msg);
                  // 关闭弹窗
                  this.visibleRecharge = false;
                  // 刷新余额
                  this.getUserDetail();
                }
              })
              .catch((error) => {
                this.$message.error(error.data.msg);
              })
              .finally(() => {
                this.submitLoading = false;
              });
          } else {
          }
        },
        // 获取充值列表
        getGatewayList() {
          getPayList().then((res) => {
            if (res.data.status === 200) {
              this.gatewayList = res.data.data.list;
            }
          });
        },

        // 充值相关结束

        async confirmMoney({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const res = await updateClientDetail(this.id, this.moneyData);
              this.$message.success(res.data.msg);
              this.visibleMoney = false;
              this.getUserDetail();
              this.submitLoading = false;
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        closeMoney() {
          this.visibleMoney = false;
          this.moneyData.amount = "";
          this.moneyData.notes = "";
          this.$refs.moneyRef && this.$refs.moneyRef.clearValidate();
          this.$refs.moneyRef && this.$refs.moneyRef.reset();
        },
        // 变更记录
        changeLog() {
          this.visibleLog = true;
          this.initChangeLog();
        },
        searchChangeLog() {
          this.moneyPage.page = 1;
          this.getChangeLog();
        },
        initChangeLog() {
          this.moneyPage.page = 1;
          this.moneyPage.keywords = "";
          this.moneyPage.type = "";
          this.getChangeLog();
        },
        // 获取变更记录列表
        async getChangeLog() {
          try {
            if (this.range.length > 0) {
              this.moneyPage.start_time =
                new Date(this.range[0].replace(/-/g, "/")).getTime() / 1000 ||
                "";
              this.moneyPage.end_time =
                new Date(this.range[1].replace(/-/g, "/")).getTime() / 1000 ||
                "";
            } else {
              this.moneyPage.start_time = "";
              this.moneyPage.end_time = "";
            }
            this.moneyLoading = true;
            const res = await getMoneyDetail(this.id, this.moneyPage);
            this.logData = res.data.data.list;
            this.logCunt = res.data.data.count;
            this.page_total_amount = res.data.data.page_total_amount;
            this.total_amount = res.data.data.total_amount;
            this.moneyLoading = false;
          } catch (error) {
            this.moneyLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        closeLog() {
          this.visibleLog = false;
        },
        // 提交修改用户信息
        updateUserInfo() {
          this.$refs.userInfo
            .validate()
            .then(async (res) => {
              if (res !== true) {
                this.$message.error(res.name[0].message);
                return;
              }
              // 验证通过
              try {
                this.submitLoading = true;
                if (this.hasClientCustom) {
                  const obj = {};
                  this.clientCustomList.forEach((item) => {
                    if (item.type === "tickbox") {
                      obj[item.id] = item.value ? "1" : "0";
                    } else if (item.type === "dropdown_text") {
                      obj[item.id] =
                        item.select_select + "|" + item.select_text;
                    } else {
                      obj[item.id] = item.value;
                    }
                  });
                  this.formData.customfield.addon_client_custom_field = obj;
                }
                if (this.hasIdcsmart_sale) {
                  const obj = {};
                  obj.id = this.idcsmart_sale_id || 0;
                  this.formData.customfield.idcsmart_sale = obj;
                }
                if (this.hasPlugin) {
                  this.formData.customfield.idcsmart_client_level = {
                    id: this.formData.level_id,
                  };
                }
                const res = await updateClient(this.id, this.formData);
                if (this.hasClientCustom) {
                  this.getClientCustomField();
                }
                this.$message.success(res.data.msg);
                this.getUserDetail();
                this.formData.password = "";
                this.formData.operate_password = "";
                this.submitLoading = false;
              } catch (error) {
                this.submitLoading = false;
                this.$message.error(error.data.msg);
              }
            })
            .catch((err) => {
              console.log(err);
            });
        },
        // 金额变更分页
        changePage(e) {
          this.moneyPage.page = e.current;
          this.moneyPage.limit = e.pageSize;
          this.getChangeLog();
        },
        changeChildPage(e) {
          this.childParams.page = e.current;
          this.childParams.limit = e.pageSize;
          this.getchildAccountList();
        },

        // 获取用户详情
        async getUserDetail() {
          try {
            const res = await getClientDetail(this.id);
            const temp = res.data.data.client;
            this.data = temp;
            // 判断是否是销售
            if (temp.customfield && temp.customfield.idcsmart_sale) {
              this.hasIdcsmart_sale = true;
              this.idcsmart_sale_list = temp.customfield.idcsmart_sale.list;
              this.idcsmart_sale_id =
                temp.customfield.idcsmart_sale.id === 0
                  ? ""
                  : temp.customfield.idcsmart_sale.id;
            }
            this.oauth = temp.oauth || [];
            this.mp_weixin_notice = temp.mp_weixin_notice || 0;
            this.formData.username = temp.username;
            this.formData.phone_code = temp.phone_code;
            this.formData.phone = temp.phone;
            this.formData.email = temp.email;
            this.formData.country_id = temp.country_id;
            this.formData.address = temp.address;
            this.formData.company = temp.company;
            this.formData.language = temp.language;
            this.formData.notes = temp.notes;
            this.formData.receive_sms = temp.receive_sms;
            this.formData.receive_email = temp.receive_email;
            // 之前单独拉取用户等级，改为直接从详情里面获取
            this.levelList = temp.customfield.idcsmart_client_level?.list;
            this.formData.level_id =
              temp.customfield.idcsmart_client_level?.id || "";
            this.devloperType = temp.customfield.idcsmart_app_market?.type;
          } catch (error) {
            console.log(error);
          }
        },
        // 获取国家列表
        async getCountry() {
          try {
            const res = await getCountry();
            this.country = res.data.data.list;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },

        // 子账户

        // 获取子账户列表
        async getchildAccountList() {
          const res = await getchildAccountListApi({
            id: this.id,
            ...this.childParams,
          });
          this.childList = res.data.data.list;
          this.childTotal = res.data.data.count;
        },
        // 去往子账户编辑页面
        goEdit(id) {
          location.href = `childAccount.htm?client_id=${id}&pId=${this.id}`;
        },
      },
    }).$mount(template);
    window.adminOperateVue = adminOperateVue;
    typeof old_onload == "function" && old_onload();
  };
})(window);

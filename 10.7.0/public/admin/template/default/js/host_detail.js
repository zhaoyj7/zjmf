(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("host-detail")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    const adminOperateVue = new Vue({
      components: {
        comConfig,
        comChooseUser,
        safeConfirm,
      },
      data() {
        return {
          urlPath: url,
          baseUrl: str,
          id: "",
          client_id: "",
          data: [],

          visible: false,
          delVisible: false,

          isLoading: false,
          diaTitle: "",
          isAgent: false,
          admin_operate_password: "",
          hostList: [],
          link_host: [],
          hostTips: "",
          self_defined_field: [],
          canTransfer: false,
          transferVisible: false,
          transfering: false,
          customForm: {},
          customRule: {},
          columns: [
            {
              width: 90,
              cell: "host_id",
              title: lang.host_transfer_text4,
              ellipsis: true,
            },
            {
              width: 120,
              cell: "product_name",
              title: lang.host_transfer_text5,
              ellipsis: true,
            },
            {
              width: 120,
              cell: "name",
              title: lang.host_transfer_text6,
              ellipsis: true,
            },
            {
              width: 150,
              cell: "notes",
              title: lang.host_transfer_text15,
              ellipsis: true,
            },
          ],
          transferForm: {
            id: "",
            client_id: "",
          },
          serverParams: {
            page: 1,
            limit: 20,
          },
          serverGroupParams: {
            page: 1,
            limit: 20,
          },
          total: 0,
          groupTotal: 0,
          loading: false,
          moneyLoading: false,
          statusVisble: false,
          title: "",
          delId: "",
          formData: {
            id: "",
            product_id: "",
            server_id: "",
            name: "",
            notes: "",
            first_payment_amount: "",
            ratio_renew: 0,
            renew_amount: "",
            billing_cycle: "",
            active_time: "",
            due_time: "",
            status: "",
            customfield: {},
            self_defined_field: {},
            upstream_host_id: null,
            base_price: undefined,
            on_demand_billing_cycle_unit: "",
            on_demand_billing_cycle_day: "",
            on_demand_billing_cycle_point: "",
            keep_time_price: null,
            on_demand_flow_price: null,
            upgrade_renew_cal: 0,
            auto_renew: 0,
            billing_cycle_name: "",
            billing_cycle_time: "",
          },
          upData: {},
          status: [],
          treeProps: {
            valueMode: "onlyLeaf",
            expandAll: true,
            label: (h, {data}) =>
              h(
                "span",
                {style: data.hidden === 1 ? "color: red" : ""},
                data.name
              ),
            keys: {
              label: "name",
              value: "key",
              children: "children",
            },
          },
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
          transferRules: {
            client_id: [
              {
                required: true,
                message: lang.select + lang.host_transfer_text13,
                type: "error",
              },
            ],
          },
          rules: {
            name: [
              {
                validator: (val) => val.length <= 100,
                message: lang.verify3 + 100,
              },
            ],
            notes: [
              {
                validator: (val) => val.length <= 1000,
                message: lang.verify3 + 1000,
              },
            ],
            first_payment_amount: [
              {
                required: true,
                message: lang.input + lang.buy_amount,
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
            renew_amount: [
              {
                required: true,
                message: lang.input + lang.renew_amount,
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
            base_price: [
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
          },
          // 变更记录
          logData: [],
          logCunt: 0,
          tableLayout: false,
          bordered: true,
          hover: true,
          statusTip: "",
          proList: [],
          currency_prefix: JSON.parse(localStorage.getItem("common_set"))
            .currency_prefix,
          serverList: [],
          cycleList: [
            {value: "free", label: lang.free},
            {value: "onetime", label: lang.onetime},
            {value: "recurring_prepayment", label: lang.recurring_prepayment},
            {value: "recurring_postpaid", label: lang.recurring_postpaid},
            {value: "on_demand", label: lang.on_demand},
            {
              value: "recurring_prepayment_on_demand",
              label: lang.on_demand_cycle,
            },
          ],
          cycleObj: {
            free: lang.free,
            onetime: lang.onetime,
            recurring_prepayment: lang.recurring_prepayment,
            recurring_postpaid: lang.recurring_postpaid,
            on_demand: lang.on_demand,
            recurring_prepayment_on_demand: lang.on_demand_cycle,
          },
          done: false,
          config: "",
          // 续费相关
          renewVisible: false,
          renewList: [],
          curId: 1,
          renewTotal: "",
          pay: false,
          submitLoading: false,
          showId: [1, 2, 3],
          curRenew: {},
          curStatus: "",
          promoList: [],
          recordColumns: [
            {
              colKey: "create_time",
              title: lang.use_time,
            },
            {
              colKey: "scene",
              title: lang.demand_scene,
            },
            {
              colKey: "order_id",
              title: lang.order_number,
            },
            {
              colKey: "promo",
              title: lang.promo_code,
              width: 220,
            },
          ],
          recordLoading: false,
          hasPlugin: false,
          tempCycle: "",
          /* 1-7 */
          moduleVisible: false,
          suspendVisible: false,
          optTilte: "",
          optType: "", // create unsuspend delete
          suspendType: [
            {
              value: "overdue",
              label: lang.overdue,
            },
            {
              value: "overtraffic",
              label: lang.overtraffic,
            },
            {
              value: "certification_not_complete",
              label: lang.certification_not_complete,
            },
            {
              value: "other",
              label: lang.other,
            },
          ],
          suspendForm: {
            suspend_auto_time: "",
            suspend_auto_time_unit: "second",
            suspend_type: "overdue",
            suspend_reason: "",
          },
          suspendAutoTimeUnit: [
            {value: "second", label: lang.suspend_auto_time_second},
            {value: "minute", label: lang.suspend_auto_time_minute},
            {value: "hour", label: lang.suspend_auto_time_hour},
            {value: "day", label: lang.suspend_auto_time_day},
          ],
          moduleLoading: false,
          isShowModule: false,
          optBtns: [],
          clientDetail: {
            id: "",
          },
          searchLoading: false,
          clientTotal: 0,
          clientList: [],
          clinetParams: {
            page: 1,
            limit: 20,
            orderby: "id",
            sort: "desc",
          },
          hasTicket: false,
          authList: JSON.parse(
            JSON.stringify(localStorage.getItem("backAuth"))
          ),
          hasTransfer: false,
          /* 新增下拉优化插件 */
          hasProPlugin: false,
          selectWay: "",
          visibleTree: false,
          productName: "",
          firstGroup: [],
          secondGroup: [],
          fir_pro: [], // 一级+商品
          second_pro: [], // 二级+商品
          fir_second_pro: [], // 一级+二级+商品
          isClick: false,
          clickExpand: [],
          hostFieldList: [],
          hostArr: [],
          tempHostId: null,
          // 手动资源
          hasResources: false,
          curResourcesId: "",
          resourceDialog: false,
          resourceList: [],
          resourceType: "", // allot free
          resourceTitle: "",
          resourceVisible: false,
          resourceId: "",
          resourcesColumns: [
            {
              colKey: "dedicated_ip",
              title: "IP",
              width: "180",
              ellipsis: true,
            },
            {
              colKey: "power_status",
              title: lang.status,
              widht: "100",
              ellipsis: true,
            },
            {
              colKey: "configuration",
              title: lang.config,
              width: "200",
              ellipsis: true,
            },
            {colKey: "notes", title: lang.notes, ellipsis: true},
            // { colKey: 'ipmi', title: 'IPMI' },
            // { colKey: 'ipmi_auth', title: lang.ipmi_auth },
            {colKey: "supplier", title: lang.manual_text10, ellipsis: true},
            {colKey: "cost", title: lang.manual_text11, ellipsis: true},
            {
              colKey: "user",
              title: lang.manual_text12,
              width: "200",
              ellipsis: true,
            },
            {
              colKey: "due_time",
              title: lang.due_time,
              width: "180",
              ellipsis: true,
            },
            {
              colKey: "opt",
              title: lang.operation,
              width: "80",
              ellipsis: true,
              fixed: "right",
            },
          ],
          connectProId: "",
          supplierList: [],
          resourceForm: {
            addon_manual_resource_supplier_id: "",
            page: 1,
            limit: 20,
            keywords: "",
            type: "",
            rel: "",
          },
          resourceTotal: 0,
          resourceLoading: false,
          // DCIM资源
          distributeType: "", // manual dcim
          distributeTitle: "",
          dcimList: [],
          dcimForm: {
            id: "",
            page: 1,
            limit: 20,
            status: "",
            server_group_id: "",
            ip: "",
          },

          dcimGroup: [],
          statusArr: [
            {value: "1", label: lang.idle},
            {value: "2", label: lang.dcim_expire},
            {value: "3", label: lang.normal},
            {value: "4", label: lang.dcim_fault},
            {value: "5", label: lang.preassemble},
            {value: "6", label: lang.lock_on},
            {value: "7", label: lang.review},
          ],
          dcimColumns: [
            {
              colKey: "id",
              title: `ID-${lang.dcim_tag}`,
              ellipsis: true,
              width: "180",
            },
            {
              colKey: "typename",
              title: lang.box_title46,
              ellipsis: true,
              width: "180",
            },
            {
              colKey: "ip",
              title: `IP(${lang.auth_num})`,
              ellipsis: true,
              width: "180",
            },
            {
              colKey: "bw",
              title: lang.bw,
              ellipsis: true,
              width: "180",
            },
            {
              colKey: "remarks",
              title: lang.notes,
              ellipsis: true,
              width: "180",
            },
            {
              colKey: "status",
              title: lang.status,
              ellipsis: true,
              width: "120",
            },
            {
              colKey: "group_name",
              title: lang.group,
              ellipsis: true,
              width: "180",
            },
            {
              colKey: "opt",
              title: lang.operation,
              width: "80",
              ellipsis: true,
              fixed: "right",
            },
          ],
          curDcimId: "",
          dcimTotal: 0,
          pageSizeOptions: [10, 20, 50, 100],
          hasDcimModule: false,
          hasNewTicket: false,
          /* 机柜 */
          cabinetForm: {
            id: "",
            page: 1,
            limit: 20,
          },
          cabinetTotal: 0,
          cabinetColumns: [
            {
              colKey: "id",
              title: "ID",
              ellipsis: true,
              width: "100",
            },
            {
              colKey: "name",
              title: lang.nickname,
              ellipsis: true,
            },
            {
              colKey: "type",
              title: lang.type,
              ellipsis: true,
            },
            {
              colKey: "ip_num",
              title: `IP(${lang.auth_num})`,
              ellipsis: true,
            },
            {
              colKey: "cabinet",
              title: lang.mf_cabinet,
              ellipsis: true,
            },
            {
              colKey: "cabinet_u",
              title: lang.mf_positiont,
              ellipsis: true,
            },
            {
              colKey: "status",
              title: lang.status,
              ellipsis: true,
              width: "120",
            },
            // {
            //   colKey: "host_id",
            //   title: `${lang.tailorism}ID`,
            //   ellipsis: true,
            // },
            // {
            //   colKey: "client_id",
            //   title: lang.belong_user,
            //   ellipsis: true,
            // },
            {
              colKey: "opt",
              title: lang.operation,
              width: "80",
              ellipsis: true,
              fixed: "right",
            },
          ],
          isSync: false,
          /* 流量包 */
          hasFlow: false,
          useableFlowList: [],
          flowDialog: false,
          curPackageId: "",
          defenceDialog: false,
          presets: {
            [lang.now_time]: new Date(),
            // 自然月 +1
            [lang.one_month_later]: new Date(
              new Date().setMonth(new Date().getMonth() + 1)
            ),
            // 自然年 +1
            [lang.one_year_later]: new Date(
              new Date().setFullYear(new Date().getFullYear() + 1)
            ),
          },
          defenceList: [],
          defenceColumns: [
            {
              colKey: "id",
              title: "ID",
            },
            {
              colKey: "host_ip",
              title: "IP",
              ellipsis: true,
              sorter: true,
            },
            {
              colKey: "defense_peak",
              title: lang.defense_peak,
              ellipsis: true,
            },
          ],
          defenceParams: {
            keywords: "",
            page: 1,
            limit: 10,
            orderby: "id",
            sort: "desc",
          },
          defenceTotal: 0,
          defenceLoading: false,
          cycleUnitList: [
            {value: "hour", label: lang.demand_per_hour},
            {value: "day", label: lang.demand_per_day},
            {value: "month", label: lang.demand_per_month},
          ],
          dayArr: [],
          checkDcimStatus: false, // false Free, true Fault
          /* 授权重置 */
          // 详情弹窗相关数据
          delVisble: false,
          delId: 0,
          detailVisible: false,
          detailData: null,
          detailLoading: false,
          historyStats: null,
          isReviewMode: false,
          // 审核相关数据
          reviewAction: "",
          rejectReasonInput: "",
          adminNotesInput: "",
          reviewLoading: false,
          auditForm: {
            host_id: "",
            new_ip: "",
            new_domain: "",
            admin_notes: "",
          },
          auditFormRules: {
            action: [
              {
                required: true,
                message: lang.authreset_action_required,
                type: "error",
              },
            ],
            reject_reason: [
              {
                required: true,
                message: lang.authreset_reject_reason_required,
                type: "error",
                trigger: "blur",
              },
            ],
            description: [
              {
                required: true,
                message: lang.authreset_quick_reply_placeholder,
                type: "error",
              },
            ],
          },
          // 快捷回复相关数据
          notesQuickReplies: [], // 备注模板
          rejectQuickReplies: [], // 拒绝理由
          quickReplyVisible: false,
          quickReplyType: "", // 'notes' 或 'reject'
          quickReplyForm: {
            id: null,
            description: "",
            type: "",
          },
          quickReplyLoading: false,
          ipDetails: {},
          allIp: ""
        };
      },
      watch: {
        "formData.billing_cycle_name"(val) {
          if (val) {
            this.formData.billing_cycle_time = this.formData.cycles.filter(
              (item) => item.billing_cycle === val
            )[0]?.duration;
          }
        },
        "formData.type": {
          immediate: true,
          handler(val) {
            this.curList =
              val === "server" ? this.serverList : this.serverGroupList;
          },
        },
        serverList() {
          this.done = this.serverList.length === this.total;
        },
        "formData.product_id": {
          handler(val) {
            if (this.selectWay === "default") {
              return;
            }
            this.calcProduct.forEach((item) => {
              item.children.forEach((items) => {
                if (items.key === val) {
                  items.checked = true;
                } else {
                  items.checked = false;
                }
              });
            });
          },
        },
        curId: {
          handler(val) {
            this.curRenew = this.renewList[val - 1];
          },
        },
      },
      created() {
        const query = this.getUrlParams();
        this.client_id = query.client_id * 1;
        this.formData.id = this.id = query.id;
        this.langList = JSON.parse(
          localStorage.getItem("common_set")
        ).lang_home;
        this.getProDetail();
        this.getproModule();
        this.getUpHostDetail();
        this.getPlugin();
        const navList = JSON.parse(localStorage.getItem("backMenus"));
        let tempArr = navList.reduce((all, cur) => {
          cur.child && all.push(...cur.child);
          return all;
        }, []);
        const curValue = tempArr.filter((item) => item.url === "client.htm")[0]
          ?.id;
        localStorage.setItem("curValue", curValue);

        this.getBtns();
        this.getUserDetail();
        this.getHostList();
        // this.getFlowList();
        for (i = 1; i <= 31; i++) {
          this.dayArr.push({
            value: i,
            label: `${i}${lang.demand_day}`,
          });
        }
      },
      computed: {
        // 当前编辑的快捷回复列表
        currentQuickReplies() {
          return this.quickReplyType === "notes"
            ? this.notesQuickReplies
            : this.rejectQuickReplies;
        },
        isDemand() {
          const demandArr = ["on_demand", "recurring_prepayment_on_demand"];
          return demandArr.includes(this.formData.billing_cycle);
        },
        calcDisableValue() {
          return (el) => {
            if (el.num && el.key === "ip") {
              return `${el.value.split(";")[0]}(${el.num})`;
            } else {
              return el.value;
            }
          };
        },
        calcAllIp() {
          return (ip) => {
            return ip.split(";");
          };
        },
        calcColumns() {
          switch (this.distributeType) {
            case "manual":
              return this.resourcesColumns;
            case "dcim":
              return this.dcimColumns;
            case "cabinet":
              return this.cabinetColumns;
          }
        },
        calcDcimStatus() {
          return (status) => {
            return this.statusArr.filter((item) => item.value === status)[0]
              ?.label;
          };
        },

        calcStatus() {
          return (status) => {
            switch (status) {
              case "on":
                return lang.manual_text14;
              case "off":
                return lang.manual_text15;
              case "error":
                return lang.manual_text16;
              default:
                return "--";
            }
          };
        },
        disabled() {
          return (
            this.formData.due_time === "" &&
            this.formData.billing_cycle === "onetime"
          );
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
          return !this.clientList.find(
            (item) => item.id === this.clientDetail.id
          );
        },
        /* 04-12 */
        calcProduct() {
          switch (this.selectWay) {
            case "first_group":
              return this.fir_pro;
            case "second_group":
              return this.second_pro;
            case "first_second_group":
              return this.fir_second_pro;
          }
        },
        calcName() {
          return (id) => {
            return this.proList.filter((item) => item.id === id)[0]?.name;
          };
        },
        calcExpand() {
          const arr = this.proList.filter(
            (item) => item.key === this.formData.product_id
          );
          return [
            "f" + arr[0]?.product_group_id_first,
            "s" + arr[0]?.product_group_id_second,
          ];
        },
      },
      filters: {
        filterMoney(money) {
          if (isNaN(money)) {
            return "0.00";
          } else {
            const temp = `${money}`.split(".");
            return parseInt(temp[0]).toLocaleString() + "." + (temp[1] || "00");
          }
        },
      },
      mounted() {},
      methods: {
        copyIp(ip) {
          if (typeof ip !== "string") {
            ip = ip.join(",");
          }
          const textarea = document.createElement("textarea");
          textarea.value = ip.replace(/,/g, "\n");
          document.body.appendChild(textarea);
          textarea.select();
          document.execCommand("copy");
          document.body.removeChild(textarea);
          this.$message.success(lang.box_text17);
        },
        async getIpDetail() {
          try {
            const res = await getAllIp({id: this.id});
            const temp = res.data.data;
            this.ipDetails = JSON.parse(JSON.stringify(res.data.data));
            this.allIp = (temp.dedicate_ip + "," + temp.assign_ip)
              .split(",")
              .filter((item) => item !== "");
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        // #region 授权重置相关
        handleResetAuth() {
          this.detailVisible = true;
          this.loadQuickRepliesByType("notes");
          this.getAuthInfo();
        },
        async getAuthInfo() {
          try {
            const res = await getAuthResetInfo({host_id: this.id});
            Object.assign(this.auditForm, res.data.data);
            this.auditForm.new_ip = this.auditForm.authorize?.ip;
            this.auditForm.new_domain = this.auditForm.authorize?.domain;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        // 关闭详情弹窗
        closeDetailDialog() {
          this.detailVisible = false;
          this.detailData = null;
          this.historyStats = null;
          this.isReviewMode = false;
          this.resetAuditForm();
          // 清除表单验证状态
          this.$nextTick(() => {
            if (this.$refs.auditForm) {
              this.$refs.auditForm.clearValidate();
            }
          });
        },
        // 重置审核表单
        resetAuditForm() {
          this.auditForm = {
            host_id: "",
            new_ip: "",
            new_domain: "",
            admin_notes: "",
          };
        },
        // 提交审核表单
        async submitAuditForm() {
          // 表单验证
          const valid = await this.$refs.auditForm.validate();
          if (!valid) {
            return;
          }
          const params = {
            host_id: this.id * 1,
            new_ip: this.auditForm.new_ip,
            new_domain: this.auditForm.new_domain,
            admin_notes: this.auditForm.admin_notes,
          };
          this.reviewLoading = true;
          try {
            const res = await authReset(params);
            if (res.data.status == 200) {
              this.$message.success(res.data.msg);
              this.closeDetailDialog();
            }
          } catch (error) {
            this.$message.error(error.data.msg);
          } finally {
            this.reviewLoading = false;
          }
        },
        // 快捷回复
        async loadQuickRepliesByType(type) {
          try {
            const res = await getQuickReplies({
              type,
              page: 1,
              limit: 100,
            });
            if (res.data.status == 200) {
              this.notesQuickReplies = res.data.data.list || [];
            }
          } catch (error) {
            console.error(
              `Failed to load quick replies for type ${type}:`,
              error
            );
          }
        },
        // 选择快捷管理员备注
        selectQuickAdminNote(description) {
          console.log("2description", description);
          this.auditForm.admin_notes = description;
        },
        // 管理快捷回复
        manageQuickReplies(type) {
          this.quickReplyType = type;
          this.quickReplyVisible = true;
          this.resetQuickReplyForm();
        },
        // 关闭快捷回复管理弹窗
        closeQuickReplyDialog() {
          this.quickReplyVisible = false;
          this.quickReplyType = "";
          this.resetQuickReplyForm();
        },
        // 重置快捷回复表单
        resetQuickReplyForm() {
          this.quickReplyForm = {
            id: null,
            description: "",
            type: this.quickReplyType,
          };
          this.$nextTick(() => {
            if (this.$refs.quickReplyForm) {
              this.$refs.quickReplyForm.clearValidate();
            }
          });
        },
        // 编辑快捷回复
        editQuickReply(item) {
          this.quickReplyForm = {
            id: item.id,
            description: item.description,
            type: item.type,
          };
        },
        // 保存快捷回复
        async saveQuickReply() {
          if (!this.quickReplyForm.description.trim()) {
            this.$message.warning(lang.authreset_content_required);
            return;
          }

          this.quickReplyLoading = true;
          try {
            const params = {
              description: this.quickReplyForm.description.trim(),
              type: this.quickReplyType,
            };

            let res;
            if (this.quickReplyForm.id) {
              // 更新
              params.id = this.quickReplyForm.id;
              res = await updateQuickReply(params);
            } else {
              // 创建
              res = await createQuickReply(params);
            }

            if (res.data.status == 200) {
              this.$message.success(res.data.msg);
              this.resetQuickReplyForm();
              this.loadQuickRepliesByType("notes");
            }
          } catch (error) {
            this.$message.error(error.data.msg);
          } finally {
            this.quickReplyLoading = false;
          }
        },
        async sureDel() {
          const params = {
            id: this.delId,
          };
          this.submitLoading = true;
          try {
            const res = await deleteAuthResetItem(params);
            if (res.data.status == 200) {
              this.$message.success(res.data.msg);
              this.getData();
              this.delVisble = false;
            }
          } catch (error) {
            this.$message.error(error.data.msg);
          } finally {
            this.submitLoading = false;
          }
        },
        // 删除快捷回复
        async deleteQuickReplyItem(item) {
          try {
            const res = await deleteQuickReply({id: item.id});
            if (res.data.status == 200) {
              this.$message.success(res.data.msg);
              this.loadQuickRepliesByType(this.quickReplyType);
            }
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        // #endregion 重置授权 end
        copyText(val) {
          copyText(val);
        },
        choosePackage(item) {
          this.curPackageId = item.id;
        },
        async handlerPackage(bol) {
          try {
            this.submitLoading = true;
            const res = await buyFlow({
              id: this.id,
              flow_packet_id: this.curPackageId,
            });
            this.$message.success(res.data.msg);
            this.submitLoading = false;
            this.flowDialog = false;
            if (bol) {
              location.href = `${this.baseUrl}order_details.htm?id=${res.data.data.id}`;
            }
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        handleFlow() {
          this.flowDialog = true;
          this.curPackageId = this.useableFlowList[0]?.id;
        },
        // async getFlowList() {
        //   try {
        //     const res = await getUsableFlow({
        //       id: this.id,
        //       page: 1,
        //       limit: 1000,
        //     });
        //     this.useableFlowList = res.data.data.list || [];
        //   } catch (error) {
        //     this.$message.error(error.data.msg);
        //   }
        // },
        jumpToPro(id) {
          location.href = `${this.baseUrl}host_detail.htm?client_id=${this.client_id}&id=${id}`;
        },
        changeCabinetPage(e) {
          this.cabinetForm.page = e.current;
          this.cabinetForm.limit = e.pageSize;
          this.getCabinetList();
        },
        /* 机柜租用 */
        handlerCabinet() {
          this.cabinetForm = {
            id: this.id,
            page: 1,
            limit: 20,
          };
          this.distributeType = "cabinet";
          this.resourceDialog = true;
          this.getCabinetList();
        },
        async getCabinetList() {
          try {
            this.resourceLoading = true;
            const params = JSON.parse(JSON.stringify(this.cabinetForm));
            params.id = this.id;
            const res = await getCabinetRent(params);
            this.dcimList = res.data.data.list;
            this.resourceLoading = false;
            this.cabinetTotal = res.data.data.count;
          } catch (error) {
            this.resourceLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        /* 机柜租用 end */
        /* 分配DCIM资源 */
        jumpDcim(url) {
          window.open(url);
        },
        changePage(e) {
          this.dcimForm.page = e.current;
          this.dcimForm.limit = e.pageSize;
          this.getDcimList();
        },

        openDefence() {
          this.defenceList = [];
          this.defenceDialog = true;
          this.searchDefence();
        },

        // 排序
        sortChange(val) {
          if (!val) {
            this.defenceParams.orderby = "id";
            this.defenceParams.sort = "desc";
          } else {
            this.defenceParams.orderby = val.sortBy;
            this.defenceParams.sort = val.descending ? "desc" : "asc";
          }
          this.getDefenceList();
        },
        clearDefenceKey() {
          this.defenceParams.keywords = "";
          this.searchDefence();
        },
        searchDefence() {
          this.defenceParams.page = 1;
          this.getDefenceList();
        },
        changeDefencePage(e) {
          this.defenceParams.page = e.current;
          this.defenceParams.limit = e.pageSize;
          this.getDefenceList();
        },
        async getDefenceList() {
          try {
            this.defenceLoading = true;
            const api = this.isAgent ? apiAngetDefenceList : apiDefenceList;
            const res = await api({
              ...this.defenceParams,
              host_id: this.id,
            });
            this.defenceList = res.data.data.list;
            this.defenceTotal = res.data.data.count;
            this.defenceLoading = false;
          } catch (error) {
            this.defenceLoading = false;
            this.$message.error(error.data.msg);
          }
        },

        changeResourcePage(e) {
          this.resourceForm.page = e.current;
          this.resourceForm.limit = e.pageSize;
          this.getResourcesList();
        },
        clearIp() {
          this.dcimForm.ip = "";
          this.getDcimList();
        },
        showDcim(server_group_id = "") {
          this.dcimForm = {
            id: "",
            page: 1,
            limit: 20,
            status: "1",
            server_group_id: server_group_id ? String(server_group_id) : "",
            ip: "",
          };
          this.resourceDialog = true;
          this.distributeType = "dcim";
          this.distributeTitle = `${lang.distribute_server}`;
          this.getDcimList();
        },
        async getDcimList() {
          try {
            this.resourceLoading = true;
            const params = JSON.parse(JSON.stringify(this.dcimForm));
            params.id = this.id;
            const res = await getDcimResource(params);
            this.dcimGroup = res.data.data.server_group;
            this.dcimList = res.data.data.list;
            this.dcimTotal = res.data.data.count;
            this.resourceLoading = false;
          } catch (error) {
            this.resourceLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        /* 分配DCIM资源 end */
        /* 手动资源 */
        async getResourcesList() {
          try {
            this.resourceLoading = true;
            const res = await getManualResource(this.resourceForm);
            this.resourceList = res.data.data.list;
            this.resourceTotal = res.data.data.count;
            this.resourceLoading = false;
            this.resourceList.forEach((item) => {
              getResourceStatus({id: item.id})
                .then((result) => {
                  item.power_status = result.data.data.status;
                })
                .catch((err) => {
                  item.power_status = err.data.data.status;
                });
            });
          } catch (error) {
            this.resourceLoading = false;
          }
        },
        clearKey() {
          this.resourceForm.keywords = "";
          this.getResourcesList();
        },
        // 获取手动资源供应商
        async getSupplierList() {
          const res = await ApiSupplier({all: 1});
          this.supplierList = res.data.data.list;
        },
        handlerDistribute(type) {
          this.resourceForm.type = type;
          this.resourceForm.keywords = "";
          this.resourceForm.addon_manual_resource_supplier_id = "";
          this.resourceDialog = true;
          this.distributeType = "manual";
          this.distributeTitle = `${lang.manual_resources}${lang.distribute}`;
          this.getResourcesList();
          this.getSupplierList();
        },
        optItem(row, type) {
          this.resourceId = row.id;
          this.resourceType = type;
          if (type === "allot") {
            if (this.curResourcesId) {
              // 当前有绑定的机器直接分配
              this.resourceTitle = lang.sure_change_resource;
            } else {
              this.resourceTitle =
                this.distributeType === "manual"
                  ? lang.sure_allot_resource
                  : lang.sure_allot_server;
            }
          } else {
            this.resourceTitle =
              this.distributeType === "manual"
                ? lang.sure_free_resource
                : lang.sure_free_server;
          }
          this.resourceVisible = true;
          this.checkDcimStatus = false;
        },
        async handlerResource() {
          try {
            this.submitLoading = true;
            if (this.distributeType === "manual") {
              const res = await changeResource(this.resourceType, {
                id: this.resourceId,
                host_id: this.id,
              });
              this.$message.success(res.data.msg);
              this.resourceVisible = false;
              this.getResourcesList();
              this.getHostField();
              this.submitLoading = false;
            } else if (
              this.distributeType === "dcim" ||
              this.resourceType === "free"
            ) {
              //const type = this.curDcimId * 1 > 0 ? "free" : "assign";
              const type = this.resourceType === "free" ? "free" : "assign";
              let params = {
                id: this.id,
                status: this.checkDcimStatus ? "Fault" : "Free",
              };
              if (type === "assign") {
                params.dcim_id = this.resourceId;
              }
              const res = await changeDcimResource(type, params);
              this.$message.success(res.data.msg);
              this.resourceVisible = false;
              this.getDcimList();
              this.getHostField();
              this.submitLoading = false;
            } else if (
              this.distributeType === "cabinet" ||
              this.resourceType === "cabinetFree"
            ) {
              const type = this.curDcimId * 1 > 0 ? "free" : "assign";
              const res = await changeCabinetResource(type, {
                id: this.id,
                dcim_id: this.resourceId,
              });
              this.$message.success(res.data.msg);
              this.resourceVisible = false;
              this.getCabinetList();
              this.getHostField();
              this.submitLoading = false;
            }
          } catch (error) {
            this.submitLoading = false;
            this.resourceVisible = false;
            this.$message.error(error.data.msg);
          }
        },
        /* 手动资源 end */
        goBack() {
          const url = sessionStorage.currentHostUrl || "";
          sessionStorage.removeItem("currentHostUrl");
          if (url) {
            location.href = url;
          } else {
            window.history.back();
          }
        },
        goClient() {
          sessionStorage.removeItem("hostListParams");
          sessionStorage.removeItem("currentHostUrl");
          location.href = "client.htm";
        },
        changePro() {
          this.id = this.formData.id;
          location.href = `host_detail.htm?id=${this.id}&client_id=${this.client_id}`;
          // this.getProDetail()
          // this.getproModule()
          // this.getUpHostDetail()
        },
        async getHostList() {
          try {
            const res = await getClientPro(this.id, {
              page: 1,
              limit: 9999,
              client_id: this.client_id,
            });
            this.hostArr = res.data.data.list;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        /* 产品内页模块输入框输出 */
        getHostField() {
          hostField(this.id).then((res) => {
            this.hostFieldList = (res.data.data || []).map((item) => {
              item.field = item.field.map((el) => {
                if (el.type === "checkbox") {
                  el.value = Boolean(Number(el.value));
                }
                return el;
              });
              return item;
            });
            const temp =
              this.hostFieldList[0]?.field.reduce((all, cur) => {
                all.push(cur.key);
                return all;
              }, []) || [];
            /* DCIM手动资源 */
            this.hasResources = temp.includes("manual_resource");
            if (this.hasResources) {
              this.curResourcesId = this.hostFieldList[0]?.field.filter(
                (item) => item.key === "manual_resource"
              )[0]?.value;
              // this.getSupplierList();
            }
            /* 云手动资源 */
            this.hasCloudResources = temp.includes("cloud_manual_resource");
            if (this.hasCloudResources) {
              this.curResourcesId = this.hostFieldList[0]?.field.filter(
                (item) => item.key === "cloud_manual_resource"
              )[0]?.value;
            }
            this.hasDcimModule = temp.includes("zjmf_dcim_id");
            if (this.hasDcimModule) {
              this.curDcimId = this.hostFieldList[0]?.field.filter(
                (item) => item.key === "zjmf_dcim_id"
              )[0]?.value;
            }
            // 机柜
            if (temp.includes("zjmf_dcim_cabinet_id")) {
              this.curResourcesId =
                this.hostFieldList[0]?.field.filter(
                  (item) => item.key === "manual_resource"
                )[0]?.value * 1;
              this.curDcimId = this.hostFieldList[0]?.field.filter(
                (item) => item.key === "zjmf_dcim_cabinet_id"
              )[0]?.value;
            }
            // 关联IP/磁盘
            if (temp.includes("rel_host_id")) {
              this.connectProId =
                this.hostFieldList[0]?.field.filter(
                  (item) => item.key === "rel_host_id"
                )[0]?.value * 1;
            }
          });
        },

        /* 新增下拉优化插件 */
        // 获取一级分组
        async getFirPro() {
          try {
            const res = await getFirstGroup();
            this.firstGroup = res.data.data.list.map((item) => {
              item.key = "f" + item.id;
              return item;
            });
            return this.firstGroup;
          } catch (error) {}
        },
        // 获取二级分组
        async getSecPro() {
          try {
            const res = await getSecondGroup();
            this.secondGroup = res.data.data.list.map((item) => {
              item.key = "s" + item.id;
              return item;
            });
            return this.secondGroup;
          } catch (error) {}
        },
        // 初始化
        init() {
          try {
            this.loading = true;
            // 获取商品，一级，二级分组
            Promise.all([
              this.getProList(),
              this.getFirPro(),
              this.getSecPro(),
            ]).then((res) => {
              // 一级+商品
              if (this.selectWay === "first_group") {
                this.fir_pro = this.firstGroup
                  .map((item) => {
                    item.children = [];
                    item.children.push(
                      ...this.proList.filter(
                        (el) => el.product_group_id_first === item.id
                      )
                    );
                    return item;
                  })
                  .filter((item) => item.children.length > 0);
              } else if (this.selectWay === "second_group") {
                // 二级+商品
                this.second_pro = this.secondGroup
                  .map((item) => {
                    item.children = [];
                    item.children.push(
                      ...this.proList.filter(
                        (el) => el.product_group_id_second === item.id
                      )
                    );
                    return item;
                  })
                  .filter((item) => item.children.length > 0);
              } else if (this.selectWay === "first_second_group") {
                // 一二级+商品
                const fArr = res[1].map((item) => {
                  let secondArr = [];
                  res[2].forEach((sItem) => {
                    if (sItem.parent_id === item.id) {
                      secondArr.push(sItem);
                    }
                  });
                  item.children = secondArr;
                  return item;
                });
                setTimeout(() => {
                  this.fir_second_pro = fArr.map((item) => {
                    item.children.map((ele) => {
                      let temp = [];
                      res[0].forEach((e) => {
                        if (e.product_group_id_second === ele.id) {
                          temp.push(e);
                        }
                      });
                      ele.children = temp;
                      return ele;
                    });
                    return item;
                  });
                }, 0);
              }
            });
          } catch (error) {
            console.log("@@@@", error);
            this.loading = false;
          }
        },
        hadelSafeConfirm(val, remember) {
          this[val]("", remember);
        },
        handelTransfer() {
          getProductTransfer(this.id).then((res) => {
            this.hostList = [res.data.data.host];
            this.link_host = res.data.data.link_host;
            this.hostTips = res.data.data.tip;
            this.canTransfer = res.data.data.transfer;
            this.transferVisible = true;
          });
        },
        onTransferSubmit({validateResult, firstError}) {
          if (validateResult === true) {
            this.transfering = true;
            transferProduct({
              id: this.id,
              client_id: this.transferForm.client_id,
            })
              .then((res) => {
                this.transfering = false;
                this.$message.success(res.data.msg);
                this.transferVisible = false;
                location.href = `host_detail.htm?id=${this.id}&client_id=${this.transferForm.client_id}`;
              })
              .catch((err) => {
                this.transfering = false;
                this.$message.error(err.data.msg);
              });
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        async getConfig() {
          try {
            const res = await getSelectConfig();
            this.selectWay = res.data.data.config;
          } catch (error) {}
        },
        focusHandler() {
          this.visibleTree = true;
          this.isClick = false;
        },
        // 商品选择
        onClick(e) {
          if (!e.node.data.children) {
            const pName = e.node.data.name;
            const pId = e.node.data.key;
            this.productName = pName;
            this.formData.product_id = pId;
            this.visibleTree = false;
          } else {
            this.isClick = true;
            // if (Array.from(this.clickExpand).toString() == [e.node.value].toString()) {
            //   this.clickExpand = []
            // } else {
            //   if (this.selectWay === 'first_second_group' && Array.from(this.clickExpand).length < 2) {
            //     this.clickExpand.push(e.node.value)
            //   } else {
            //     this.clickExpand = [e.node.value]
            //   }

            // }
          }
        },
        getLabel(createElement, node) {
          const label = node.data.name;
          const {data} = node;
          data.label = label;
          return label;
        },
        /* 新增下拉优化插件 end*/
        changeUser(id) {
          this.client_id = id;
          this.id = id;
          location.href = `client_host.htm?client_id=${this.client_id}`;
        },
        changeProUser(val) {
          this.transferForm.client_id = val;
        },
        async getUpHostDetail() {
          try {
            const res = await upHostDetail(this.id);
            this.upData = res.data.data.host.host;
          } catch (error) {
            console.log(error.data.msg);
          }
        },
        filterMethod(search, option) {
          return option;
        },
        calcFieldOption(item) {
          return item.split(",");
        },
        // 获取用户详情
        async getUserDetail() {
          try {
            const res = await getClientDetail(this.client_id);
            this.clientDetail = res.data.data.client;
          } catch (error) {}
        },

        async getOperate() {
          try {
            const res = await getMoudleOperate({
              id: this.id,
            });
            this.$nextTick(() => {
              $("#operateBox") && $("#operateBox").html(res.data.data.content);
            });
          } catch (error) {}
        },

        /* 1-31 操作按钮由后端返回 */
        async getBtns() {
          try {
            const res = await getMoudleBtns({
              id: this.id,
            });
            // 权限
            const authObj = {
              create: "auth_business_host_detail_create_account",
              suspend: "auth_business_host_detail_suspend_account",
              unsuspend: "auth_business_host_detail_unsuspend_account",
              terminate: "auth_business_host_detail_terminate_account",
              renew: "auth_business_host_detail_host_renew",
              push: "auth_host_push_downstream",
              sync: "",
            };
            this.optBtns = res.data.data.button.map((item) => {
              item.auth = authObj[item.func];
              return item;
            });
          } catch (error) {}
        },
        /* 1-7 start */
        handlerMoudle(type) {
          this.optType = type;
          switch (type) {
            case "create":
              this.optTilte = lang.module_tip1;
              break;
            case "unsuspend":
              this.optTilte = lang.module_tip2;
              break;
            case "terminate":
              this.optTilte = lang.module_tip3;
              break;
            case "sync":
              this.optTilte = lang.module_tip5;
              break;
            case "push":
              this.optTilte = lang.module_tip6;
              break;
            case "suspend":
              this.optTilte = lang.module_tip4;
              this.handlerSuspend();
              break;
            case "renew":
              this.renewDialog();
          }
          if (type !== "renew" && type !== "suspend") {
            this.moduleVisible = true;
          }
        },
        confirmModule() {
          switch (this.optType) {
            case "create":
              return this.createHandler();
              break;
            case "unsuspend":
              return this.unsuspendHandler();
            case "terminate":
              return this.deleteHandler();
            case "sync":
              return this.clickSyncInfo();
            case "push":
              return this.clickPushInfo();
          }
        },
        async clickPushInfo(e, remember_operate_password = 0) {
          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            this.moduleLoading = true;
            const res = await pushHostInfo({
              id: this.id,
              admin_operate_password,
              remember_operate_password,
            });
            this.$message.success(res.data.msg);
            this.getProDetail();
            this.getUpHostDetail();
            this.getBtns();
            this.moduleLoading = false;
            this.moduleVisible = false;
          } catch (error) {
            this.moduleLoading = false;
            if (error.data.data) {
              if (
                !admin_operate_password &&
                error.data.data.operate_password === 1
              ) {
                return this.$refs.safeRef.openDialog("clickPushInfo");
              }
            }
            this.$message.error(error.data.msg);
          }
        },
        async clickSyncInfo() {
          try {
            this.moduleLoading = true;
            const res = await syncInfo(this.id);
            this.$message.success(res.data.msg);
            this.getProDetail();
            this.getUpHostDetail();
            this.getBtns();
            this.moduleLoading = false;
            this.moduleVisible = false;
          } catch (error) {
            this.moduleLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        // 开通
        async createHandler(e, remember_operate_password = 0) {
          // if (!this.admin_operate_password) {
          //   this.$refs.safeRef.openDialog("createHandler");
          //   return;
          // }
          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            this.moduleLoading = true;
            const res = await createModule({
              id: this.id,
              admin_operate_password,
              // admin_operate_methods: "createHandler",
              remember_operate_password,
            });
            this.$message.success(res.data.msg);
            this.getProDetail();
            this.getUpHostDetail();
            this.getBtns();
            this.moduleLoading = false;
            this.moduleVisible = false;
          } catch (error) {
            this.moduleLoading = false;
            if (error.data.data) {
              if (
                !admin_operate_password &&
                error.data.data.operate_password === 1
              ) {
                return this.$refs.safeRef.openDialog("createHandler");
              }
            }
            this.$message.error(error.data.msg);
          }
        },
        // 取消停用
        async unsuspendHandler(e, remember_operate_password = 0) {
          // if (!this.admin_operate_password) {
          //   this.$refs.safeRef.openDialog("unsuspendHandler");
          //   return;
          // }
          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            this.moduleLoading = true;
            const res = await unsuspendModule({
              id: this.id,
              admin_operate_password,
              // admin_operate_methods: "unsuspendHandler",
              remember_operate_password,
            });
            this.$message.success(res.data.msg);
            this.getProDetail();
            this.getUpHostDetail();
            this.getBtns();
            this.moduleLoading = false;
            this.moduleVisible = false;
          } catch (error) {
            this.moduleLoading = false;
            if (error.data.data) {
              if (
                !admin_operate_password &&
                error.data.data.operate_password === 1
              ) {
                return this.$refs.safeRef.openDialog("unsuspendHandler");
              }
            }
            this.$message.error(error.data.msg);
          }
        },
        // 删除
        async deleteHandler(e, remember_operate_password = 0) {
          // if (!this.admin_operate_password) {
          //   this.$refs.safeRef.openDialog("deleteHandler");
          //   return;
          // }
          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            this.moduleLoading = true;
            const res = await delModule({
              id: this.id,
              admin_operate_password,
              // admin_operate_methods: "deleteHandler",
              remember_operate_password,
            });
            this.$message.success(res.data.msg);
            this.getProDetail();
            this.getBtns();
            this.getUpHostDetail();

            this.moduleLoading = false;
            this.moduleVisible = false;
          } catch (error) {
            this.moduleLoading = false;
            if (error.data.data) {
              if (
                !admin_operate_password &&
                error.data.data.operate_password === 1
              ) {
                return this.$refs.safeRef.openDialog("deleteHandler");
              }
            }
            this.$message.error(error.data.msg);
          }
        },
        // 暂停
        handlerSuspend() {
          this.suspendForm.suspend_type = "overdue";
          this.suspendForm.suspend_reason = "";
          this.suspendForm.suspend_auto_time = "";
          this.suspendForm.suspend_auto_time_unit = "second";
          this.suspendVisible = true;
        },
        // 计算自动取消时间
        calculateAutoUnsuspendTime() {
          const autoUnsuspendTimeValue = this.suspendForm.suspend_auto_time;
          const autoUnsuspendTimeUnit =
            this.suspendForm.suspend_auto_time_unit === "second"
              ? 1 * 1000
              : this.suspendForm.suspend_auto_time_unit === "minute"
              ? 60 * 1000
              : this.suspendForm.suspend_auto_time_unit === "hour"
              ? 3600 * 1000
              : 86400 * 1000;
          const autoUnsuspendTime =
            autoUnsuspendTimeValue * autoUnsuspendTimeUnit;

          return Math.trunc((Date.now() + autoUnsuspendTime) / 1000);
        },
        // 提交停用
        async onSubmit(e, remember_operate_password = 0) {
          // if (!this.admin_operate_password) {
          //   this.$refs.safeRef.openDialog("onSubmit");
          //   return;
          // }
          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            this.moduleLoading = true;
            const res = await suspendModule({
              id: this.id,
              suspend_type: this.suspendForm.suspend_type,
              suspend_reason: this.suspendForm.suspend_reason,
              auto_unsuspend_time: this.suspendForm.suspend_auto_time
                ? this.calculateAutoUnsuspendTime()
                : "",
              // admin_operate_methods: "onSubmit",
              admin_operate_password,
              remember_operate_password,
            });
            this.$message.success(res.data.msg);
            this.getProDetail();
            this.getBtns();
            this.getUpHostDetail();

            this.moduleLoading = false;
            this.suspendVisible = false;
          } catch (error) {
            this.moduleLoading = false;
            if (error.data.data) {
              if (
                !admin_operate_password &&
                error.data.data.operate_password === 1
              ) {
                return this.$refs.safeRef.openDialog("onSubmit");
              }
            }
            this.$message.error(error.data.msg);
          }
        },
        /* 1-7 end */
        async getPlugin() {
          try {
            const res = await getAddon();
            const temp = res.data.data.list.reduce((all, cur) => {
              all.push(cur.name);
              return all;
            }, []);
            this.hasPlugin = temp.includes("PromoCode");
            this.hasTicket = temp.includes("IdcsmartTicket");
            this.hasNewTicket = temp.includes("TicketPremium");
            this.hasPlugin && this.getPromoList();
            this.hasProPlugin = temp.includes("ProductDropDownSelect");
            this.hasTransfer = temp.includes("HostTransfer");
            this.hasFlow = temp.includes("FlowPacket");
            if (this.hasProPlugin) {
              this.getConfig();
            } else {
              this.selectWay = "default";
            }
          } catch (error) {}
        },
        // 获取优惠码使用记录
        async getPromoList() {
          try {
            const res = await proPromoRecord({id: this.id});
            this.promoList = res.data.list;
          } catch (error) {
            console.log(error);
          }
        },
        jumpOrder(row) {
          location.href = str + `order.htm?order_id=${row.order_id}`;
        },
        /* 跳转到订单/工单 */
        jumpToOrder() {
          location.href =
            str + `client_order.htm?id=${this.client_id}&host_id=${this.id}`;
        },
        jumpToTicket() {
          if (this.hasNewTicket) {
            return (location.href =
              str +
              `plugin/ticket_premium/client_ticket.htm?id=${this.client_id}&host_id=${this.id}`);
          }
          if (!this.hasNewTicket && this.hasTicket) {
            return (location.href =
              str +
              `plugin/idcsmart_ticket/client_ticket.htm?id=${this.client_id}&host_id=${this.id}`);
          }
        },
        /* 续费 */
        renewDialog() {
          this.getRenewPage();
        },
        // 获取续费页面
        async getRenewPage() {
          try {
            const res = await getSingleRenew(this.formData.id);
            this.renewList = res.data.data.host.map((item, index) => {
              item.id = index + 1;
              return item;
            });
            if (this.renewList.length === 0) {
              return this.$message.warning(lang.renew_tip);
            }
            this.renewVisible = true;
            this.curRenew = this.renewList[0];
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        // 向左移动
        subIndex() {
          let num = this.curId;
          if (num > 1) {
            num -= 1;
            this.curId -= 1;
          }
          if (this.showId[0] > 1) {
            let newIds = this.showId;
            newIds[0] = newIds[0] - 1;
            newIds[1] = newIds[1] - 1;
            newIds[2] = newIds[2] - 1;
            this.showId = newIds;
          }
        },
        // 向右移动
        addIndex() {
          let num = this.curId;
          if (num < this.renewList.length) {
            num += 1;
            this.curId = num++;
          }
          if (this.showId[2] < this.renewList.length) {
            let newIds = this.showId;
            newIds[0] = newIds[0] + 1;
            newIds[1] = newIds[1] + 1;
            newIds[2] = newIds[2] + 1;
            this.showId = newIds;
          }
        },
        checkCur(item) {
          this.curId = item.id;
        },
        async submitRenew(e, remember_operate_password = 0) {
          // if (!this.admin_operate_password) {
          //   this.$refs.safeRef.openDialog("submitRenew");
          //   return;
          // }

          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            this.submitLoading = true;
            const temp = JSON.parse(JSON.stringify(this.curRenew));
            delete temp.id;
            const params = {
              id: this.formData.id,
              pay: this.pay,
              ...temp,
              admin_operate_password,
              // admin_operate_methods: "submitRenew",
              remember_operate_password,
            };
            const res = await postSingleRenew(params);
            this.$message.success(res.data.msg);
            this.submitLoading = false;
            this.renewVisible = false;
            this.getProDetail();
            this.getUpHostDetail();
          } catch (error) {
            this.submitLoading = false;
            if (error.data.data) {
              if (
                !admin_operate_password &&
                error.data.data.operate_password === 1
              ) {
                return this.$refs.safeRef.openDialog("submitRenew");
              }
            }
            this.$message.error(error.data.msg);
          }
        },
        back() {
          this.delVisible = true;
        },
        // 删除
        deltePro(row) {
          this.delVisible = true;
        },
        async onConfirm(e, remember_operate_password = 0) {
          const admin_operate_password = this.admin_operate_password;
          this.admin_operate_password = "";
          try {
            this.submitLoading = true;
            const res = await deletePro({
              id: this.id,
              admin_operate_password,
              // admin_operate_methods: "onConfirm",
              remember_operate_password,
            });
            this.$message.success(res.data.msg);
            this.delVisible = false;
            setTimeout(() => {
              this.submitLoading = false;
              window.location = document.referrer;
            }, 300);
          } catch (error) {
            this.submitLoading = false;
            this.delVisible = false;
            if (error.data.data) {
              if (
                !admin_operate_password &&
                error.data.data.operate_password === 1
              ) {
                return this.$refs.safeRef.openDialog("onConfirm");
              }
            }
            this.$message.error(error.data.msg);
          }
        },

        getUrlParams() {
          const url = window.location.href;
          // 判断是否有参数
          if (url.indexOf("?") === -1) {
            return {};
          }
          const params = url.split("?")[1];
          const paramsArr = params.split("&");
          const paramsObj = {};
          paramsArr.forEach((item) => {
            const key = item.split("=")[0];
            const value = item.split("=")[1];
            paramsObj[key] = value;
          });
          return paramsObj;
        },
        getQuery(val) {
          return val.split("=")[1];
        },
        checkTime(val) {
          if (moment(val).unix() > moment(this.formData.due_time).unix()) {
            return {result: false, message: lang.verify6, type: "error"};
          }
          return {result: true};
        },
        checkTime1(val) {
          if (moment(val).unix() < moment(this.formData.active_time).unix()) {
            return {result: false, message: lang.verify6, type: "error"};
          }
          return {result: true};
        },
        changeActive() {
          this.$refs.userInfo.validate({
            fields: ["active_time", "due_time"],
          });
        },
        async getproModule() {
          try {
            const res = await getproModule(this.id);
            this.isShowModule = res.data.data.content ? true : false;
            this.$nextTick(() => {
              $(".config-box .content").html(res.data.data.content);
            });
          } catch (error) {}
        },
        async getProList() {
          try {
            const res = await getProList();
            const temp = res.data.data.list.map((item) => {
              item.key = "t" + item.id;
              return item;
            });
            // 处理老财务迁移过后的数据：产品id不在产品列表中
            let hasPro = temp.some(
              (item) => this.formData.product_id === item.key
            );
            if (!hasPro) {
              temp.unshift({
                name: this.formData.product_name,
                key: this.formData.product_id,
              });
            }
            this.proList = temp;
            return this.proList;
          } catch (error) {}
        },
        changeType(type) {
          this.formData.type = type;
          this.formData.rel_id = "";
        },
        // 修改
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
                this.isLoading = true;
                const params = {...this.formData};
                params.product_id = params.product_id.replace("t", "");
                params.due_time = params.due_time === "" ? 0 : params.due_time;
                params.active_time =
                  params.active_time === "" ? 0 : params.active_time;
                if (params.active_time === 0) {
                  params.active_time = moment(params.active_time * 1000).format(
                    "YYYY-MM-DD HH:mm:ss"
                  );
                }
                if (params.due_time === 0) {
                  params.due_time = moment(params.due_time * 1000).format(
                    "YYYY-MM-DD HH:mm:ss"
                  );
                }
                // 修改前台
                const obj = {};
                const tempArr = JSON.parse(
                  JSON.stringify(this.hostFieldList)
                ).reduce((all, cur) => {
                  all.push(...cur.field);
                  return all;
                }, []);
                tempArr.forEach((item) => {
                  if (item.type === "checkbox") {
                    item.value = item.value ? 1 : 0;
                  }
                  if (this.hasDcimModule) {
                    // dicm 模块只传  disable 不存在的参数
                    if (!item.disable) {
                      obj[item.key] = item.value;
                    }
                  } else {
                    obj[item.key] = item.value;
                  }
                });
                params.customfield.module_admin_field = obj;
                params.self_defined_field = this.customForm;
                // 修改前台结束
                const res = await updateProduct(this.id, params);
                this.$message.success(res.data.msg);
                this.getProDetail();
                this.getUpHostDetail();
                this.isLoading = false;
              } catch (error) {
                this.isLoading = false;
                this.$message.error(error.data.msg);
              }
            })
            .catch((err) => {
              console.log(err);
            });
        },
        // 获取用户详情
        async getProDetail() {
          try {
            let inter = await getInterface(this.serverParams);
            this.serverList = inter.data.data.list;
            this.total = inter.data.data.count;
            if (this.total > 20) {
              this.serverParams.limit = this.total;
              inter = await getInterface(this.serverParams);
              this.serverList = inter.data.data.list;
            }

            const res = await getProductDetail(this.id);
            const temp = res.data.data.host;
            this.isAgent = res.data.data?.host.agent === 1;
            if (this.isAgent) {
              this.getIpDetail();
            }
            // 是否为本地商品代理
            this.isSync = res.data.data.host.mode === "sync";
            Object.assign(this.formData, temp);
            this.formData.product_id = "t" + temp.product_id;
            this.formData.active_time = temp.active_time
              ? moment(temp.active_time * 1000).format("YYYY-MM-DD HH:mm:ss")
              : "";
            this.formData.due_time = temp.due_time
              ? moment(temp.due_time * 1000).format("YYYY-MM-DD HH:mm:ss")
              : "";
            this.formData.server_id =
              temp.server_id === 0 ? "" : temp.server_id;
            this.tempCycle = temp.billing_cycle;
            this.tempHostId = temp.upstream_host_id;
            this.$forceUpdate();
            this.getHostField();
            this.status = res.data.data.status.map((item, index) => {
              return {value: item, label: lang[item]};
            });
            const obj = {};
            this.self_defined_field = res.data.data.self_defined_field || [];
            res.data.data.self_defined_field.forEach((item) => {
              if (item.field_type === "tickbox") {
                obj[item.id + ""] = item.value === "1";
              } else {
                obj[item.id + ""] = item.value;
              }
            });
            this.$set(this, "customForm", obj);
            this.curStatus = this.formData.status;
            if (this.formData.status === "Active") {
              this.$checkPermission(
                "auth_business_host_detail_module_operate"
              ) && this.getOperate();
            }
            document.title =
              lang.user_list +
              "-" +
              temp.product_name +
              "-" +
              localStorage.getItem("back_website_name");
            this.init();
          } catch (error) {
            console.log(error);
          }
        },
        // 续费
        async renew() {
          try {
            const res = await getSingleRenew(this.id);
            console.log(res);
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
      },
    }).$mount(template);
    window.adminOperateVue = adminOperateVue;
    typeof old_onload == "function" && old_onload();
  };
})(window);

(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    typeof old_onload == "function" && old_onload();
    window.lang = Object.assign(window.lang, window.plugin_lang);
    const { showToast } = vant;
    const app = Vue.createApp({
      components: {
        topMenu,
        curSelect,
      },
      data() {
        return {
          lang: window.lang,
          // 分页
          params: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
            status: [3],
            ticket_type_id: "",
          },
          ticketData: {
            title: "",
            ticket_type_id: "",
            host_ids: "",
            content: "",
            attachment: [],
            // 工单部门id
            admin_role_id: "",
          },
          hasApp: false,
          rules: {
            title: [
              {
                required: true,
                message: lang.ticket_tips9,
                trigger: "blur",
              },
            ],
            ticket_type_id: [
              {
                required: true,
                message: lang.ticket_tips2,
                trigger: "blur",
              },
            ],
            content: [
              {
                required: true,
                message: lang.ticket_tips6,
                trigger: "blur",
              },
            ],
          },
          // 通用数据
          commonData: {},
          // 表格数据
          dataList: [],
          // 表格加载
          tableLoading: false,
          tableFinished: false,
          // 创建工单弹窗是否显示
          isShowDialog: false,
          // 创建工单弹窗 数据
          formData: {
            title: "",
            ticket_type_id: "",
            host_ids: [],
            content: "",
            attachment: [],
          },
          // 表单错误信息显示
          errText: "",
          // 工单类别
          ticketType: [],
          ticketStatus: [],
          // 关联产品列表
          hostList: [],
          createBtnLoading: false,
          loading: false,
          fileList: [],
          configObj: {
            ticket_notice_open: 0,
            ticket_notice_description: "",
          },
          statusIsChange: false,
        };
      },
      computed: {
        calcTicketType() {
          return [
            {
              value: "",
              text: lang.all,
            },
          ].concat(this.ticketType);
        },
        calcStatus() {
          const arrName = [];
          this.ticketStatus.forEach((item) => {
            if (this.params.status.includes(item.id)) {
              arrName.push(item.name);
            }
          });
          return arrName.length > 1
            ? arrName[0] + "  +" + (arrName.length - 1).toString()
            : arrName.join("、");
        },
        calcHostName() {
          const item = this.hostList.find((item) => {
            return item.id == this.ticketData.host_ids;
          });
          if (item) {
            return item.product_name + "(" + item.name + ")";
          } else {
            return "";
          }
        },
      },

      created() {
        // 获取通用信息
        this.getCommonData();
        this.getOrderConfig();
        // 获取工单类型
        this.getTicketType();
        // 获取工单状态
        this.getTicketStatus();
        // 获取关联产品列表
        this.getHostList();
      },
      mounted() {
        this.hasApp = havePlugin("IdcsmartAppMarket");
      },
      methods: {
        handleRenew(item) {
          const hostId = item.parent_host_id || item.id;
          window.open(`/productdetail.htm?id=${hostId}`);
        },
        goBack() {
          window.history.back();
        },
        async getOrderConfig() {
          try {
            const res = await getOrderConfig();
            this.configObj = res.data.data;
          } catch (error) {}
        },

        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          document.title =
            this.commonData.website_name + "-" + lang.ticket_label15;
        },
        handeSelecFile() {
          this.$refs.uploadRef.chooseFile();
        },
        afterRead(file) {
          const arr = [];
          if (file instanceof Array) {
            arr.push(...file);
          } else {
            arr.push(file);
          }
          this.uploadFiles(arr);
        },
        handeDelFile(file, index) {
          this.ticketData.attachment = this.ticketData.attachment.filter(
            (item) => {
              return item != file.save_name;
            }
          );
          this.fileList.splice(index, 1);
        },
        delAllFile() {
          this.ticketData.attachment = [];
          this.fileList = [];
        },
        uploadFiles(arr) {
          arr.forEach((item) => {
            const formData = new FormData();
            formData.set("file", item.file); // 这里要用set,如果用append，还是会出现一起上传的情况
            uploadFile(formData)
              .then((res) => {
                console.log(res);
                if (res.data.status === 200) {
                  this.fileList.push({
                    file: item.file,
                    save_name: res.data.data.save_name,
                  });
                  this.ticketData.attachment.push(res.data.data.save_name);
                }
              })
              .catch((err) => {
                showToast(err.data.msg);
              });
          });
        },
        initPage() {
          this.params.page = 1;
          this.dataList = [];
          // 获取列表
          this.getTicketList();
        },
        // 获取工单列表
        getTicketList() {
          this.tableLoading = true;
          ticketList(this.params)
            .then((res) => {
              if (res.data.status === 200) {
                this.dataList = this.dataList.concat(res.data.data.list);
                this.tableLoading = false;
                this.params.page++;
                this.params.total = res.data.data.count;
                this.tableFinished =
                  this.dataList.length >= res.data.data.count;
              }
            })
            .catch((err) => {
              this.tableLoading = false;
              showToast(err.data.msg);
            });
        },
        // 展示创建弹窗 并初始化弹窗数据
        showCreateDialog() {
          location.href = `addTicket.htm`;
        },
        handelMore() {},
        calcProductName(item) {
          const curTime = parseInt(new Date().getTime() / 1000);
          if (item.due_time > 0) {
            if (item.due_time <= curTime) {
              // 已到期
              return `(${lang.ticket_label21})`;
            } else if (item.due_time - curTime <= item.renewal_first_day_time) {
              // 即将到期
              return `(${item.name.slice(0, 8)}......${item.name.slice(-5)})${
                lang.ticket_label22
              }`;
            } else {
              return `(${item.name})`;
            }
          } else {
            return `(${item.name})`;
          }
        },
        calcShowRenew(item) {
          const curTime = parseInt(new Date().getTime() / 1000);
          if (
            item.due_time > 0 &&
            (item.due_time <= curTime ||
              item.due_time - curTime <= item.renewal_first_day_time)
          ) {
            return true;
          }
        },
        onSubmit() {
          this.$refs.ticketForm.validate().then(() => {
            this.loading = true;
            const params = { ...this.ticketData };
            params.host_ids = params.host_ids ? [params.host_ids] : [];
            createTicket(params)
              .then((res) => {
                if (res.data.status == 200) {
                  const id = res.data.data.id;
                  location.href = `ticketDetails.htm?id=${id}`;
                }
                this.loading = false;
              })
              .catch((error) => {
                this.loading = false;
                showToast(error.data.msg);
              });
          });
        },
        statusClose() {
          if (this.statusIsChange) {
            this.initPage();
            this.statusIsChange = false;
          }
        },

        clickStatus(item) {
          if (this.params.status.includes(item)) {
            this.params.status.splice(this.params.status.indexOf(item), 1);
          } else {
            this.params.status.push(item);
          }
          this.statusIsChange = true;
        },
        openAddTicket() {
          this.ticketData = {
            title: "",
            ticket_type_id: "",
            host_ids: "",
            content: "",
            attachment: [],
            // 工单部门id
            admin_role_id: "",
          };
          this.$refs.ticketForm && this.$refs.ticketForm.resetValidation();
          this.isShowDialog = true;
        },
        closeDialog() {
          this.delAllFile();
          this.isShowDialog = false;
        },
        hexToRgb(hex) {
          const color = hex.split("#")[1];
          const r = parseInt(color.substring(0, 2), 16);
          const g = parseInt(color.substring(2, 4), 16);
          const b = parseInt(color.substring(4, 6), 16);
          return `rgba(${r},${g},${b},0.12)`;
        },
        // 获取工单类型
        getTicketType() {
          ticketType().then((res) => {
            if (res.data.status === 200) {
              this.ticketType = res.data.data.list.map((item) => {
                item.value = item.id;
                item.text = item.name;
                return item;
              });
            }
          });
        },
        // 获取工单状态列表
        getTicketStatus() {
          ticketStatus().then((res) => {
            if (res.data.status === 200) {
              this.params.status = [3];
              this.ticketStatus = res.data.data.list.map((item) => {
                item.value = item.id;
                item.text = item.name;
                return item;
              });
              res.data.data.list.forEach((item) => {
                if (item.status === 0) {
                  this.params.status.push(item.id);
                }
              });
              // 获取工单列表
              this.initPage();
            }
          });
        },
        // 获取产品列表
        getHostList() {
          const params = {
            keywords: "",
            status: "",
            page: 1,
            limit: 1000,
            orderby: "id",
            sort: "desc",
            scene: "ticket",
          };
          hostAll(params).then((res) => {
            if (res.data.status === 200) {
              this.hostList = res.data.data.list
                .filter((item) => item.status !== "Deleted")
                .map((item) => {
                  const curTime = parseInt(new Date().getTime() / 1000);
                  item.isDue = item.due_time > 0 && item.due_time <= curTime;
                  return item;
                });
            }
          });
        },
        chooseItem(e) {
          const val = e[0].id;
          const cur = this.hostList.filter((el) => el.id === val);
          if (this.hasApp && cur.length > 0 && cur[0].isDue) {
            this.ticketData.host_ids = "";
          }
        },

        // 跳转工单详情
        itemReply(record) {
          const id = record.id;
          location.href = `ticketDetails.htm?id=${id}`;
        },
        // 关闭工单
        itemClose(record) {
          const id = record.id;
          const params = {
            id,
          };
          // 调用关闭工单接口 给出结果
          closeTicket(params)
            .then((res) => {
              if (res.data.status === 200) {
                showToast(res.data.msg);
                this.initPage();
              }
            })
            .catch((err) => {
              showToast(err.data.msg);
            });
        },
        // 催单
        itemUrge(record) {
          const nowDate = new Date().getTime();
          const last_reply_time = record.last_urge_time * 1000;
          if (nowDate - last_reply_time < 1000 * 60 * 15) {
            showToast(lang.ticket_label16);
            return;
          }
          const id = record.id;
          const params = { id };
          // 调用催单接口 给出结果
          urgeTicket(params)
            .then((res) => {
              if (res.data.status === 200) {
                showToast(res.data.msg);
              }
            })
            .catch((err) => {
              showToast(err.data.msg);
            });
        },

        titleClick(record) {
          const id = record.id;
          location.href = `ticketDetails.htm?id=${id}`;
        },
      },
    });
    window.directiveInfo.forEach((item) => {
      app.directive(item.name, item.fn);
    });
    app.use(vant).mount("#template");
  };
})(window);

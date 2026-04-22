(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = Object.assign(window.lang, window.plugin_lang);
    new Vue({
      components: {
        asideMenu,
        topMenu,
      },
      created() {
        // 获取通用信息
        this.getCommonData();
        this.getHostList();
        this.getTicketType();
        // this.getDepartment()
      },
      mounted() {
        // this.initTemplate()
        this.addons_js_arr = JSON.parse(
          document.querySelector("#addons_js").getAttribute("addons_js")
        ); // 插件列表
        const arr = this.addons_js_arr.map((item) => {
          return item.name;
        });
        if (arr.includes("IdcsmartAppMarket")) {
          this.hasApp = true;
        }
      },
      updated() {
        // // 关闭loading
        document.getElementById("mainLoading").style.display = "none";
        document.getElementsByClassName("template")[0].style.display = "block";
      },
      destroyed() {},
      data() {
        return {
          commonData: {},
          ticketData: {
            title: "",
            ticket_type_id: "",
            host_ids: "",
            content: "",
            attachment: [],
            // 工单部门id
            admin_role_id: "",
          },
          ticketType: [],
          hostList: [],
          departmentList: [],
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
          jwt: `Bearer ${localStorage.jwt}`,
          loading: false,
          fileList: [],
          hostId: "",
          sureDialog: false,
          hasApp: false,
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
      },
      computed: {
        calcShowRenew() {
          return (item) => {
            const curTime = parseInt(new Date().getTime() / 1000);
            if (
              item.due_time > 0 &&
              (item.due_time <= curTime ||
                item.due_time - curTime <= item.renewal_first_day_time)
            ) {
              return true;
            }
          };
        },
        calcProductName() {
          return (item) => {
            const curTime = parseInt(new Date().getTime() / 1000);
            if (item.due_time > 0) {
              if (item.due_time <= curTime) {
                // 已到期
                return `(${lang.ticket_label21})`;
              } else if (
                item.due_time - curTime <=
                item.renewal_first_day_time
              ) {
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
          };
        },
      },
      methods: {
        handleRenew(item) {
          this.hostId = item.parent_host_id || item.id;
          this.sureDialog = true;
        },
        subJump() {
          this.sureDialog = false;
          window.open(`/productdetail.htm?id=${this.hostId}`);
        },
        chooseItem(val) {
          const cur = this.hostList.filter((el) => el.id === val);
          if (this.hasApp && cur.length > 0 && cur[0].isDue) {
            this.ticketData.host_ids = "";
          }
        },
        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          document.title =
            this.commonData.website_name + "-" + lang.ticket_label14;
        },
        // 返回工单列表页面
        backTicket() {
          location.href = "ticket.htm";
        },
        // 载入富文本
        initTemplate() {
          tinymce.init({
            selector: "#tiny",
            language_url: "/tinymce/langs/zh_CN.js",
            language: "zh_CN",
            min_height: 400,
            width: "100%",
            plugins:
              "link lists image code table colorpicker textcolor wordcount contextmenu fullpage",
            toolbar:
              "bold italic underline strikethrough | fontsizeselect | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote | undo redo | link unlink image fullpage code | removeformat",
            images_upload_url: "/console/v1/upload",
            convert_urls: false,
            images_upload_handler: this.handlerAddImg,
          });
        },
        // 富文本图片上传
        handlerAddImg(blobInfo, success, failure) {
          return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append("file", blobInfo.blob());
            axios
              .post("/console/v1/upload", formData, {
                headers: {
                  Authorization: "Bearer" + " " + localStorage.getItem("jwt"),
                },
              })
              .then((res) => {
                const json = {};
                if (res.status !== 200) {
                  failure("HTTP Error: " + res.data.msg);
                  return;
                }
                json.location = res.data.data?.image_url;
                if (!json || typeof json.location !== "string") {
                  failure("Error:" + res.data.msg);
                  return;
                }
                success(json.location);
              });
          });
        },
        // 新建工单确认点击
        onSubmit() {
          // this.ticketData.content = tinyMCE.activeEditor.getContent()
          // console.log(this.ticketData);
          this.$refs["form"].validate((valid) => {
            if (valid && !this.loading) {
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
                  this.$message.error(error.data.msg);
                });
            } else {
              return false;
            }
          });
        },
        // 获取工单类型
        getTicketType() {
          ticketType().then((res) => {
            if (res.data.status === 200) {
              this.ticketType = res.data.data.list;
              // this.ticketData.ticket_type_id = this.ticketType[0]?.id
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
              [].filter;
              this.hostList = res.data.data.list
                .filter((item) => item.status !== "Deleted")
                .map((item) => {
                  const curTime = parseInt(new Date().getTime() / 1000);
                  if (item.due_time > 0 && item.due_time <= curTime) {
                    item.isDue = true;
                  } else {
                    item.isDue = false;
                  }
                  return item;
                });
              // .filter((item) => {
              //   return item.status === "Active";
              // });
            }
          });
        },
        getDepartment() {
          department().then((res) => {
            if (res.data.status == 200) {
              this.departmentList = res.data.data.list;
              this.ticketData.admin_role_id =
                this.departmentList[0].admin_role_id;
              this.getTicketType(this.ticketData.admin_role_id);
            }
          });
        },
        departmentChange(e) {
          this.ticketData.ticket_type_id = "";
          if (e) {
            this.getTicketType(e);
          }
        },
        beforeRemove(file, fileList) {
          // 获取到删除的 save_name
          let save_name = file.response.data.save_name;
          this.ticketData.attachment = this.ticketData.attachment.filter(
            (item) => {
              return item != save_name;
            }
          );
        },
        // 上传文件相关
        handleSuccess(response, file, fileList) {
          // console.log(response);
          if (response.status != 200) {
            this.$message.error(response.msg);
            // 清空上传框
            let uploadFiles = this.$refs["fileupload"].uploadFiles;
            let length = uploadFiles.length;
            uploadFiles.splice(length - 1, length);
          } else {
            this.ticketData.attachment.push(response.data.save_name);
          }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    window.lang = Object.assign(window.lang, window.plugin_lang);
    typeof old_onload == "function" && old_onload();
    const { showToast, showImagePreview } = vant;
    const app = Vue.createApp({
      components: {
        topMenu,
      },
      data() {
        return {
          lang: window.lang,
          commonData: {},
          id: null,
          isShowMore: false,
          ticketData: {},
          // 工单类别
          ticketTypeList: [],
          // 关联产品列表
          hostList: [],
          sdasaasd: { maxHeight: ".9867rem", minHeight: 0 },
          // 基本信息
          baseMsg: {
            title: "",
            type: "",
            hosts: "",
            status: "",
            create_time: "",
            color: "#0AB39C",
          },
          replyData: {
            id: null,
            content: "",
            attachment: [],
          },
          sendBtnLoading: false,
          fileList: [],
          visible: false,
          delLoading: false,
          isClose: false,
          viewer: null,
          preImg: "",
        };
      },
      mounted() { },
      updated() { },
      destroyed() { },
      created() {
        // 获取通用信息
        this.getCommonData();

        // 获取工单详情
        this.getDetails();
        this.autoRefresh();
      },
      watch: {},
      filters: {},
      methods: {
        autoRefresh() {
          setInterval(() => {
            this.getDetails();
          }, 1000 * 60);
        },
        goBack() {
          location.href = "ticket.htm";
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
        uploadFiles(arr) {
          arr.forEach((item) => {
            const formData = new FormData();
            formData.set("file", item.file); // 这里要用set,如果用append，还是会出现一起上传的情况
            uploadFile(formData)
              .then((res) => {
                if (res.data.status === 200) {
                  this.fileList.push({
                    file: item.file,
                    save_name: res.data.data.save_name,
                  });
                  this.replyData.attachment.push(res.data.data.save_name);
                }
              })
              .catch((err) => {
                showToast(err.data.msg);
              });
          });
        },
        handeDelFile(file, index) {
          this.replyData.attachment = this.replyData.attachment.filter(
            (item) => {
              return item != file.save_name;
            }
          );
          this.fileList.splice(index, 1);
        },
        // 使 right-main 的滚动条平滑的滚动到底部
        scrollToBottom() {
          window.scrollTo({
            top: document.body.scrollHeight,
          });
        },
        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          document.title =
            this.commonData.website_name + "-" + lang.ticket_label17;
        },
        // 返回工单列表页面
        backTicket() {
          location.href = "ticket.htm";
        },
        hexToRgb(hex) {
          const color = hex.split("#")[1];
          const r = parseInt(color.substring(0, 2), 16);
          const g = parseInt(color.substring(2, 4), 16);
          const b = parseInt(color.substring(4, 6), 16);
          return `rgba(${r},${g},${b},0.12)`;
        },
        // 获取url中的id参数然后获取工单详情信息
        getDetails() {
          let url = window.location.href;
          let getqyinfo = url.split("?")[1];
          let getqys = new URLSearchParams("?" + getqyinfo);
          let id = getqys.get("id");
          this.id = id;
          const params = {
            id,
          };
          // 调用查看工单接口
          ticketDetail(params).then((res) => {
            if (res.data.status === 200) {
              this.ticketData = res.data.data.ticket;
              const replies = res.data.data.ticket.replies;
              const arrEntities = {
                lt: "<",
                gt: ">",
                nbsp: " ",
                amp: "&",
                quot: '"',
              };
              this.ticketData.replies = replies.reverse().map((item) => {
                item.content = filterXSS(
                  item.content.replace(
                    /&(lt|gt|nbsp|amp|quot);/gi,
                    function (all, t) {
                      return arrEntities[t];
                    }
                  )
                ).replace(/&(lt|gt|nbsp|amp|quot);/gi, function (all, t) {
                  return arrEntities[t];
                });
                item.content = item.content.replaceAll(
                  'http-equiv="refresh"',
                  ""
                );
                // === 新增：只针对 iframe ===
                item.content = item.content.replace(
                  /<iframe[\s\S]*?>[\s\S]*?<\/iframe>/gi,
                  ""
                );

                // 兜底：防止 iframe 自闭合 / 畸形
                item.content = item.content.replace(/<iframe[\s\S]*?>/gi, "");
                return item;
              });
              // 工单类型
              this.getTicketType();
              // 当前状态
              this.baseMsg.status = this.ticketData.status;
              // 标题
              this.baseMsg.title = this.ticketData.title;
              this.baseMsg.create_time = this.ticketData.create_time;
              this.baseMsg.color = this.ticketData.color;
              // 关联产品
              this.getHostList();
              this.$nextTick(() => {
                this.scrollToBottom();
              });
            }
          });
        },
        // 获取工单类型
        getTicketType() {
          ticketType().then((res) => {
            if (res.data.status === 200) {
              this.ticketTypeList = res.data.data.list;
              this.ticketTypeList.map((item) => {
                if (item.id == this.ticketData.ticket_type_id) {
                  this.baseMsg.type = item.name;
                }
              });
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
          };
          hostAll(params).then((res) => {
            if (res.data.status === 200) {
              this.hostList = res.data.data.list;
              // let names = ""

              let hosts = [];
              this.ticketData.host_ids.forEach((element) => {
                this.hostList.forEach((item) => {
                  if (item.id == element) {
                    let hostitem = {
                      id: item.id,
                      label: item.product_name + " (" + item.name + ")",
                    };

                    hosts.push(hostitem);

                    // names += item.product_name + " (" + item.name + ")" + ","
                  }
                });
              });
              // names = names.slice(0, -1)
              this.baseMsg.hosts = hosts;
            }
          });
        },
        // 回复工单
        doReplyTicket() {
          if (this.sendBtnLoading) return;
          if (!this.replyData.content) {
            showToast(lang.ticket_label18);
            return;
          }

          // 将content中的 /n 替换成 <br>
          this.replyData.content = this.replyData.content.replace(
            /\n/g,
            "<br>"
          );
          const params = {
            ...this.replyData,
            id: this.id,
          };
          this.sendBtnLoading = true;
          replyTicket(params)
            .then((res) => {
              if (res.data.status === 200) {
                // 清空输入框
                this.replyData.content = "";
                this.replyData.attachment = [];
                this.fileList = [];
                // 重新拉取工单详情
                this.getDetails();
              }
              this.sendBtnLoading = false;
            })
            .catch((err) => {
              this.sendBtnLoading = false;
              console.log(err);
              showToast(err.data.msg);
            });
        },
        // 上传文件相关
        handleSuccess(response, file, fileList) {
          // console.log(response);
          if (response.status != 200) {
            showToast(response.msg);
            // 清空上传框
            let uploadFiles = this.$refs["fileupload"].uploadFiles;
            let length = uploadFiles.length;
            uploadFiles.splice(length - 1, length);
          } else {
            this.replyData.attachment.push(response.data.save_name);
          }
        },
        handleProgress(response) {
          console.log("response", response);
        },
        beforeRemove(file, fileList) {
          // 获取到删除的 save_name
          let save_name = file.response.data.save_name;
          this.replyData.attachment = this.replyData.attachment.filter(
            (item) => {
              return item != save_name;
            }
          );
        },
        // 附件下载
        downloadfile(item) {
          const url = item.url;
          const name = item.name;
          const type = name.substring(name.lastIndexOf(".") + 1);
          if (
            [
              "png",
              "jpg",
              "jepg",
              "jpeg",
              "JPEG",
              "bmp",
              "webp",
              "PNG",
              "JPG",
              "JEPG",
              "BMP",
              "WEBP",
            ].includes(type)
          ) {
            showImagePreview([url]);
          } else {
            window.open(url);
          }
        },
        showClose() {
          this.visible = true;
        },
        // 关闭工单
        doCloseTicket() {
          const params = {
            id: this.id,
          };
          this.delLoading = true;
          closeTicket(params)
            .then((res) => {
              if (res.data.status == 200) {
                showToast(res.data.msg);
                this.visible = false;
                // 重新拉取工单详情
                this.getDetails();
              }
              this.delLoading = false;
            })
            .catch((error) => {
              showToast(error.data.msg);
              this.delLoading = false;
            });
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
        toHost(id) {
          location.href = "/productdetail.htm?id=" + id;
        },
        hanldeImage(event) {
          console.log(event);
          if (
            event.target.nodeName == "IMG" ||
            event.target.nodeName == "img"
          ) {
            const img = event.target.currentSrc;
            showImagePreview([img]);
          }
        },
      },
    });
    window.directiveInfo.forEach((item) => {
      app.directive(item.name, item.fn);
    });
    app.use(vant).mount("#template");
  };
})(window);

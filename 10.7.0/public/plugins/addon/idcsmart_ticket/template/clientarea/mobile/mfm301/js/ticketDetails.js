(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    window.lang = Object.assign(window.lang, window.plugin_lang);
    typeof old_onload == "function" && old_onload();
    const { showToast, showLoadingToast, closeToast, showImagePreview } = vant;
    const app = Vue.createApp({
      components: {
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
          quoteReplyItem: null,
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
            this.getDetails(true);
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
          let rightMain = document.getElementsByClassName("ticket-content")[0];
          rightMain.scrollTop = rightMain.scrollHeight;
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
        filterContent(content) {
          const arrEntities = {
            lt: "<",
            gt: ">",
            nbsp: " ",
            amp: "&",
            quot: '"',
          };
          return filterXSS(
            content.replace(
              /&(lt|gt|nbsp|amp|quot);/gi,
              function (all, t) {
                return arrEntities[t];
              }
            )
          ).replace(/&(lt|gt|nbsp|amp|quot);/gi, function (all, t) {
            return arrEntities[t];
          }).replace(
            /<iframe[\s\S]*?>[\s\S]*?<\/iframe>/gi,
            ""
          ).replace(/<iframe[\s\S]*?>/gi, "").replace(
            'http-equiv="refresh"',
            ""
          )
        },
        // 获取url中的id参数然后获取工单详情信息
        getDetails(isAutoRefresh = false) {
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
              this.ticketData.replies = replies.reverse().map((item) => {
                item.content = this.filterContent(item.content);
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
              // 滚动到输入框位置
              if (!isAutoRefresh) {
                this.$nextTick(() => {
                  this.toBottom();
                });
              }
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
        // 处理引用HTML内容 - 清理截断的标签，保留完整HTML结构
        getQuoteHtml(item) {
          if (!item || !item.content) return "";

          try {
            let content = item.content || "";

            // 步骤1: 移除末尾不完整的标签
            content = content.replace(/<[^>]*\s*$/, "").trim();

            // 步骤2: 尝试提取并补全body标签
            const bodyMatch = content.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
            if (bodyMatch && bodyMatch[1]) {
              // 只返回body内的内容
              return bodyMatch[1];
            }

            // 如果没有body标签，移除DOCTYPE、html、head等外层标签
            content = content.replace(/<!DOCTYPE[^>]*>/gi, "");
            content = content.replace(/<\/?html[^>]*>/gi, "");
            content = content.replace(/<head[^>]*>[\s\S]*?<\/head>/gi, "");

            return this.filterContent(content.trim());
          } catch (e) {
            console.error("getQuoteHtml error:", e);
            return item.content || "";
          }
        },
        // 根据引用的消息ID获取完整的内容（包含图片）
        getQuotedItemContent(quoteId) {
          if (!quoteId) return "";

          // 从replyList中找到对应ID的完整消息
          const quotedItem = this.ticketData.replies.find(
            (item) => item.id === quoteId
          );

          if (quotedItem && quotedItem.content) {
            return this.getQuoteHtml(quotedItem);
          }

          return "";
        },
        // 根据引用的消息ID获取完整的附件列表
        getQuotedItemAttachments(quoteId) {
          if (!quoteId) return [];

          // 从ticketData.replies中找到对应ID的完整消息
          const quotedItem =
            this.ticketData.replies &&
            this.ticketData.replies.find((item) => item.id === quoteId);

          if (
            quotedItem &&
            quotedItem.attachment &&
            Array.isArray(quotedItem.attachment)
          ) {
            return quotedItem.attachment;
          }

          return [];
        },
        // 通过ID滚动到被引用的消息
        scrollToQuotedMessageById(replyId) {
          // 使用 data-reply-id 属性找到对应的元素
          const targetElement = document.querySelector(
            `[data-reply-id="${replyId}"]`
          );

          if (targetElement) {
            targetElement.scrollIntoView({
              behavior: "smooth",
              block: "center",
            });

            // 添加高亮效果
            targetElement.classList.add("highlight-flash");
            setTimeout(() => {
              targetElement.classList.remove("highlight-flash");
            }, 2000);
          }
        },
        // 点击引用按钮
        replyItem(item) {
          // 设置引用对象
          this.quoteReplyItem = item;

          // 滚动到输入框位置
          this.$nextTick(() => {
            this.toBottom();
          });
        },
        // 取消引用
        cancelQuoteReply() {
          this.quoteReplyItem = null;
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
          showLoadingToast({
            message: lang.ticket_label25,
            duration: 0,
          });

          // 将content中的 /n 替换成 <br>
          this.replyData.content = this.replyData.content.replace(
            /\n/g,
            "<br>"
          );
          const params = {
            ...this.replyData,
            id: this.id,
            // 添加引用回复ID
            quote_reply_id: this.quoteReplyItem ? this.quoteReplyItem.id : 0,
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
                // 清空引用
                this.quoteReplyItem = null;
                this.getDetails();
                closeToast();
              }
              this.sendBtnLoading = false;
            })
            .catch((err) => {
              this.sendBtnLoading = false;
              console.log(err);
              showToast(err.data.msg);
              closeToast();
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
        // 聊天框滚动到底部
        toBottom() {
          const ticketBottom = document.querySelector(".ticket-content");
          console.log(ticketBottom);
          if (ticketBottom) {
            ticketBottom.scrollTop = ticketBottom.scrollHeight;
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
                this.getDetails(true);
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

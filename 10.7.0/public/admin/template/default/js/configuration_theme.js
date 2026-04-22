(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("configuration-theme")[0];

    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comTinymce,
        comConfig,
      },
      mixins: [fixedFooter],
      data () {
        return {
          formData: {
            cart_instruction: 0,
            clientarea_theme_mobile_switch: "0",
            web_switch: "0",
            cart_change_product: "0",
          },
          value: "clientarea_theme",
          clientarea_type: "global_theme",
          clinetarea_switch: "pc",
          isCanUpdata: sessionStorage.isCanUpdata === "true",
          clientarea_theme: [],
          web_theme_list: [],
          cart_theme_list: [],
          cart_theme_mobile_list: [],
          clientarea_theme_mobile_list: [],
          rules: {
            clientarea_theme: [
              {
                required: true,
                message: lang.input + lang.site_name,
                type: "error",
              },
              {
                validator: (val) => val.length <= 255,
                message: lang.verify3 + 255,
                type: "warning",
              },
            ],
          },
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `${trigger.offsetWidth}px`,
            }),
          },
          submitLoading: false,
          hasController: true,
          host_model: "",
          /* 主题配置 */
          // 图片上传相关
          uploadUrl: str + "v1/upload",
          uploadHeaders: {
            Authorization: "Bearer" + " " + localStorage.getItem("backJwt"),
          },
          loading: false,
          // banner
          bannerColumns: [
            {
              colKey: "drag",
              width: 30,
              className: "drag-icon",
            },
            { colKey: "img", title: lang.tem_banner, width: "300" },
            {
              colKey: "url",
              title: lang.jump_link,
              width: "200",
              ellipsis: true,
            },
            {
              colKey: "time",
              title: lang.tem_time_range,
              width: "230",
              ellipsis: true,
            },
            {
              colKey: "show",
              title: lang.info_config_text7,
              width: "100",
              ellipsis: true,
            },
            {
              colKey: "notes",
              title: lang.tem_notes,
              width: "150",
              ellipsis: true,
            },
            {
              colKey: "op",
              title: lang.operation,
              width: "100",
              fixed: "right",
            },
          ],
          tempBanner: [],
          editFile: [],
          editItem: {
            id: "",
            url: "",
            img: [],
            show: false,
            notes: "",
            edit: false,
            timeRange: [],
          },
          delVisible: false,
          curId: "",
          optType: "",
          themeConfigVisible: false,
          currentTheme: "",
          currentThemeConfig: {
            display_time: 3
          },
          tempTime: 0,
          submtiBannerLoading: false,
        };
      },
      created () {
        const queryName = this.getQuery("name");
        if (queryName) {
          this.value = queryName;
        }
        const navList = JSON.parse(localStorage.getItem("backMenus"));
        let tempArr = navList.reduce((all, cur) => {
          cur.child && all.push(...cur.child);
          return all;
        }, []);
        this.getActivePlugin();
        document.title =
          lang.theme_setting + "-" + localStorage.getItem("back_website_name");
      },
      mounted () {
        this.getTheme();
      },
      methods: {
        /* 主题配置 */
        handleThemeConfig (theme) {
          this.themeConfigVisible = true;
          this.currentTheme = theme;
          this.getSpecifyTheme();
          this.getBannerList();
          event.stopPropagation();
        },
        async getSpecifyTheme () {
          try {
            const res = await getSpecifyThemeConfig({
              theme: this.currentTheme
            });
            this.currentThemeConfig = res.data.data;
            this.tempTime = this.currentThemeConfig.display_time;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        async saveSpecifyTheme (e, tag) {
          try {
            if (tag === 'time' && e === this.tempTime) {
              return;
            }
            let display_time = this.currentThemeConfig.display_time;
            display_time = (display_time <= 0 ? 0 : display_time) || 0
            const res = await saveSpecifyThemeConfig({
              theme: this.currentTheme,
              display_one: this.currentThemeConfig.display_one,
              display: this.currentThemeConfig.display,
              display_time
            });
            this.$message.success(res.data.msg);
            this.getSpecifyTheme();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        /* 轮播图 */
        addBanner () {
          this.tempBanner = this.tempBanner
            .filter((item) => item.id)
            .map((item) => {
              item.edit = false;
              return item;
            });
          this.tempBanner.push({
            url: "",
            img: "",
            start_time: "",
            end_time: "",
            show: 0,
            notes: "",
            edit: true,
            timeRange: [],
          });
          this.editItem = {
            id: "",
            url: "",
            img: [],
            show: 0,
            notes: "",
            edit: false,
            timeRange: [],
          };
          this.optType = "add";
        },
        handlerEdit (row) {
          this.tempBanner = this.tempBanner
            .filter((item) => item.id)
            .map((item) => {
              item.edit = false;
              return item;
            });
          row.edit = true;
          this.optType = "update";
          this.editItem = JSON.parse(JSON.stringify(row));
          this.editItem.img = [{ url: row.img }];
          this.editItem.timeRange = [
            moment.unix(row.start_time).format("YYYY/MM/DD"),
            moment.unix(row.end_time).format("YYYY/MM/DD"),
          ];
        },
        delteItem (row) {
          this.delVisible = true;
          this.curId = row.id;
        },
        async changeShow (e, row) {
          try {
            if (row.edit) {
              this.editItem.show = e;
              return false;
            }
            const res = await showBanner({
              id: row.id,
              show: e,
            });
            this.$message.success(res.data.msg);
            this.getBannerList();
          } catch (error) {
            this.$message.error(error.data.msg);
            this.getBannerList();
          }
        },
        async sureDel () {
          try {
            this.submitLoading = true;
            const res = await deleteBanner(
              {
                id: this.curId,
              });
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.getBannerList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        cancelItem (row, index) {
          if (!row.id) {
            this.tempBanner.splice(index, 1);
          }
          row.edit = false;
        },
        formatResponse (res) {
          if (res.status !== 200) {
            this.uploadTip = "";
            this.files = [];
            this.$message.error(res.msg);
            return { error: res.msg, url: res.url };
          }
          return { save_name: res.data.save_name, url: res.data.image_url };
        },
        async saveItem (row, index) {
          try {
            const temp = JSON.parse(JSON.stringify(this.editItem));
            if (temp.img.length === 0) {
              return this.$message.error(`${lang.upload}${lang.picture}`);
            }
            if (temp.timeRange.length === 0) {
              return this.$message.error(`${lang.select}${lang.time}`);
            }
            // if (!temp.url) {
            //   return this.$message.error(`${lang.input}${lang.feed_link}`);
            // }
            const reg =
              /^(((ht|f)tps?):\/\/)?([^!@#$%^&*?.\s-]([^!@#$%^&*?.\s]{0,63}[^!@#$%^&*?.\s])?\.)+[a-z]{2,6}\/?/;
            if (temp.url && !reg.test(temp.url)) {
              return this.$message.error(`${lang.input}${lang.feed_tip}`);
            }
            temp.start_time = parseInt(
              new Date(temp.timeRange[0].replaceAll("-", "/")).getTime() / 1000
            );
            temp.end_time = parseInt(
              new Date(temp.timeRange[1].replaceAll("-", "/")).getTime() / 1000
            );
            if (temp.lastModified) {
              temp.img = temp.img[0]?.response.data.image_url;
            } else {
              temp.img = temp.img[0].url;
            }
            temp.edit = false;
            if (this.optType === "add") {
              delete temp.id;
            }
            temp.theme = this.currentTheme;
            if (this.submtiBannerLoading) {
              return false;
            }
            this.submtiBannerLoading = true;
            const res = await addAndUpdateBanner(this.optType, temp);
            this.$message.success(res.data.msg);
            this.getBannerList();
            this.submtiBannerLoading = false;
          } catch (error) {
            this.submtiBannerLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        onDrop () { },
        async onDragSort (params) {
          try {
            this.tempBanner = params.currentData;
            const arr = this.tempBanner.reduce((all, cur) => {
              all.push(cur.id);
              return all;
            }, []);
            const res = await sortBanner({ id: arr });
            this.$message.success(res.data.msg);
            this.getBannerList();
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        async getBannerList () {
          try {
            this.loading = true;
            const res = await getBanner({
              theme: this.currentTheme,
            });
            this.tempBanner = res.data.data.list.map((item) => {
              item.edit = false;
              return item;
            });
            this.loading = false;
          } catch (error) {
            this.loading = false;
            this.$message.error(error.data.msg);
          }
        },
        /* 轮播图 end */
        /* 主题配置 end */
        changeClient (val) {
          if (val === "cart_theme" && this.formData.cart_instruction === 1) {
            this.$nextTick(() => {
              this.$refs.comTinymce &&
                this.$refs.comTinymce.setContent(
                  this.formData.cart_instruction_content
                );
            });
          }
        },
        getHostModelList (name, type) {
          return (
            this.formData?.module_list?.find((item) => item.name === name)?.[
            type
            ] || []
          );
        },
        changeCartInstruction (val) {
          if (val === 1) {
            this.$nextTick(() => {
              this.$refs.comTinymce &&
                this.$refs.comTinymce.setContent(
                  this.formData.cart_instruction_content
                );
            });
          }
        },
        changeTab (value) {
          if (value === "cart_theme") {
            this.$nextTick(() => {
              this.$refs.comTinymce &&
                this.$refs.comTinymce.setContent(
                  this.formData.cart_instruction_content
                );
            });
          }
        },
        getQuery (name) {
          const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
          const r = window.location.search.substr(1).match(reg);
          if (r != null) return decodeURI(r[2]);
          return null;
        },
        async getActivePlugin () {
          const res = await getActiveAddon();
          this.hasController = (res.data.data.list || [])
            .map((item) => item.name)
            .includes("TemplateController");
        },
        jumpController (item) {
          event.stopPropagation();
          location.href = `${location.origin}/${location.pathname.split("/")[1]
            }/${item.url}?theme=${item.name}`;
        },

        selectTheme (type, name, host_model) {
          if (host_model) {
            // this.formData[type][host_model] = this.formData[type][host_model] == "" ? name : "";
            this.formData[type][host_model] = name;
          } else {
            this.formData[type] = name;
          }
        },

        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;

              const params = { ...this.formData };
              if (this.$refs.comTinymce) {
                params.cart_instruction_content =
                  this.$refs.comTinymce.getContent() || "";
              }
              const res = await updateThemeConfig(params);
              this.$message.success(res.data.msg);
              this.getTheme();
              this.submitLoading = false;
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        async getTheme () {
          try {
            const res = await getThemeConfig();
            const temp = res.data.data;
            this.formData = Object.assign({}, temp);
            this.formData.cart_theme_mobile =
              temp.clientarea_theme_mobile_switch == 0
                ? temp.cart_theme_mobile_list[0]?.name || ""
                : temp.cart_theme_mobile;

            this.formData.clientarea_theme_mobile =
              temp.clientarea_theme_mobile_switch == 0
                ? temp.clientarea_theme_mobile_list[0]?.name || ""
                : temp.clientarea_theme_mobile;
            this.$refs.comTinymce.setContent(temp.cart_instruction_content);
          } catch (error) { }
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

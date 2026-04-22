(function (window, undefined) {
  var old_onload = window.onload;

  window.onload = function () {
    const host = location.origin;
    const fir = location.pathname.split("/")[1];
    const str = `${host}/${fir}/`;
    const isEn = localStorage.getItem("backLang") === "en-us" ? true : false;
    if (isEn) {
      document.getElementById("layout").className = "isEn";
    } else {
      document.getElementById("layout").className = "";
    }
    // 全局搜索
    function globalSearch(keywords) {
      return Axios.get(`/global_search?keywords=${keywords}`);
    }
    TDesign.Dialog.options.props.closeOnOverlayClick.default = false;
    TDesign.Dialog.options.props.placement.default = "center";
    TDesign.Form.options.props.resetType = "initial";
    const aside = document.getElementById("aside");
    const footer = document.getElementById("footer");
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;

    // const linkTag = document.querySelector('link[rel="icon"]')
    // linkTag.href = localStorage.getItem('tab_logo')
    if (!localStorage.getItem("backJwt")) {
      location.href = str + "/login.htm";
    }
    const MODE_OPTIONS = [
      {
        type: "light",
        text: window.lang.theme_light,
        src: `${url}/img/assets-setting-light.svg`,
      },
      {
        type: "dark",
        text: window.lang.theme_dark,
        src: `${url}/img/assets-setting-dark.svg`,
      },
    ];
    const COLOR_OPTIONS = [
      "default",
      "cyan",
      "green",
      "yellow",
      "orange",
      "red",
      "pink",
      "purple",
    ];
    /* aside */
    if (aside) {
      new Vue({
        components: {
          comConfig,
        },
        data: {
          baseUrl: str,
          collapsed: false,
          isSearchFocus: false,
          searchData: "",
          /* 系统设置 */
          visible: false,
          formData: {
            mode: localStorage.getItem("theme-mode") || "light",
            brandTheme: localStorage.getItem("theme-color") || "default",
            clientTheme: "default",
          },
          MODE_OPTIONS,
          COLOR_OPTIONS,
          colorList: {
            DEFAULT: {
              "@brand-color": "#0052D9",
            },
            CYAN: {
              "@brand-color": "#0594FA",
            },
            GREEN: {
              "@brand-color": "#00A870",
            },
            ORANGE: {
              "@brand-color": "#ED7B2F",
            },
            RED: {
              "@brand-color": "#E34D59",
            },
            PINK: {
              "@brand-color": "#ED49B4",
            },
            PURPLE: {
              "@brand-color": "#834EC2",
            },
            YELLOW: {
              "@brand-color": "#EBB105",
            },
          },
          curSrc: localStorage.getItem("country_imgUrl") || `${url}/img/CN.png`,
          langList: [],
          expanded: [],
          curValue: Number(localStorage.getItem("curValue")),
          iconList: [
            "user",
            "view-module",
            "cart",
            "setting",
            "folder-open",
            "precise-monitor",
            "control-platform",
          ],
          clientThemeList: [
            {
              color: "#1A56DB",
              name: "default",
            },
            {
              color: "#B30500",
              name: "theme9",
            },
            {
              color: "#059669",
              name: "theme2",
            },
            {
              color: "#EE8220",
              name: "theme3",
            },
            {
              color: "#DB2777",
              name: "theme4",
            },
            {
              color: "#7C3AED",
              name: "theme5",
            },
            {
              color: "#E53935",
              name: "theme6",
            },
            {
              color: "#292739",
              name: "theme7",
            },
            {
              color: "#FFFFFF",
              secondaryColor: "#1A56DB",
              name: "theme1",
            },
            {
              color: "#FFFFFF",
              secondaryColor: "#8B8CBE",
              name: "theme8",
            },
          ],
          navList: [],
          audio_tip: null,
          global: null,
          loadingSearch: false,
          noData: false,
          timer: null,
          isShow: false,
          userName: localStorage.getItem("userName") || "-",
          // 修改密码弹窗
          editPassVisible: false,
          editPassFormData: {
            password: "",
            repassword: "",
          },
          isCanUpdata: false,
          pluginUpgrade: false,
          setting_parent_id: null,
          plugin_parent_id: null,
          /* 全局搜索 */
          globalConfig: [],
          globalForm: {
            table: "",
            key: "",
            value: "",
          },
          curSearchType: "",
          curSearchOptions: [],
          treeProps: {
            valueMode: "onlyLeaf",
            keys: {
              label: "name",
              value: "id",
              children: "children",
            },
          },
          popupProps: {
            overlayInnerStyle: (trigger) => ({
              width: `300px`,
              attach: "#search-key",
            }),
          },
          searchLoading: false,
          msgType: "idcsmart",
          loadingMsg: false,
          systemMsgList: [],
          idcsmartMsgList: [],
          systemCount: 0,
          idcsmartCount: 0,
          scheduleCount: 0,
          versionData: {},
          messageVisible: false,
          hasSchedule: false,
          scheduleList: [],
        },
        computed: {
          logUrl() {
            const commonSet = JSON.parse(
              localStorage.getItem("common_set") || "{}"
            );
            const adminLogo = `${location.origin}/upload/common/default/${commonSet.admin_logo}`;
            if (this.collapsed) {
              return adminLogo || `${url}/img/small-logo.png`;
            } else {
              return adminLogo || `${url}/img/logo.png`;
            }
          },
          calcFiled() {
            if (!this.globalForm.table) {
              return;
            }
            const temp = this.globalConfig.filter(
              (item) => item.table === this.globalForm.table
            )[0].field;
            this.globalForm.key = temp[0].key;
            this.curSearchType = temp[0]?.type;
            this.curSearchOptions = temp[0]?.option;
            this.globalForm.value = "";
            return temp;
          },
          total_count() {
            return this.systemCount + this.idcsmartCount + this.scheduleCount;
          },
        },
        mounted() {
          const customCollapsed = JSON.parse(
            localStorage.getItem("customCollapsed")
          );
          if (customCollapsed === null) {
            this.collapsed =
              /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
                navigator.userAgent
              );
          } else {
            this.collapsed = customCollapsed;
          }

          this.navList = JSON.parse(localStorage.getItem("backMenus"));
          this.navList.forEach((item) => {
            item.child &&
              item.child.forEach((el) => {
                if (el.id === this.curValue) {
                  this.expanded = [];

                  this.expanded.push(item.id);
                }
                if (el.url === "configuration_system.htm") {
                  // 如果是系统设置 找到他的parent_id
                  this.setting_parent_id = el.parent_id;
                }
                if (el.url === "plugin.htm") {
                  // 如果是插件列表 找到他的parent_id
                  this.plugin_parent_id = el.parent_id;
                }
              });
          });
          this.langList = JSON.parse(
            localStorage.getItem("common_set")
          ).lang_admin;
          // 其他区域关闭全局搜索
          document.onclick = () => {
            this.isShow = false;
          };
          this.$nextTick(() => {
            document.getElementById(`search-content`) &&
              (document.getElementById(`search-content`).onclick = () => {
                event.stopPropagation();
              });
            document.getElementById(`global-input`) &&
              (document.getElementById(`global-input`).onclick = () => {
                event.stopPropagation();
              });
          });
        },
        created() {
          // this.getSystemConfig()
          this.setWebTitle();
          this.getVersion();
          this.getNewVersion();
          this.getGlobalConfig();
          this.getCommonSetting();
          this.syncNotice();
          this.getNoticeList();
          this.getPlugin();
        },
        methods: {
          goMessageList() {
            location.href = `${str}message_list.htm?type=${this.msgType}`;
          },
          goMesgDetail(id) {
            location.href = `${str}message_detail.htm?id=${id}&type=${this.msgType}`;
          },
          goScheduleList() {
            location.href = `${str}plugin/schedule/index.htm`;
          },
          getNoticeList() {
            this.loadingMsg = true;
            const params = {
              read: 0,
              page: 1,
              limit: 5,
            };
            // apiNoticeList({...params, type: "system"}).then((res) => {
            //   this.systemMsgList = res.data.data.list;
            //   this.systemCount = res.data.data.count;
            //   this.loadingMsg = false;
            //   this.total_count = res.data.data.total_count;
            // });
            apiNoticeList({ ...params, type: "idcsmart" }).then((res) => {
              this.idcsmartMsgList = res.data.data.list;
              this.idcsmartCount = res.data.data.count;
              // this.total_count = res.data.data.total_count;
              this.loadingMsg = false;
              this.messageVisible = this.idcsmartMsgList.some(
                (item) => item.priority === 1
              );
            });
          },
          async handleIgnoreSchedule(item) {
            try {
              const res = await ignoreSchedule({ id: item.id });
              this.$message.success(res.data.msg);
              this.getScheduleList();
            } catch (error) {
              this.$message.error(error.data.msg);
            }
          },
          async handleAcceptSchedule(item) {
            try {
              const res = await acceptSchedule({ id: item.id });
              this.$message.success(res.data.msg);
              this.getScheduleList();
            } catch (error) {
              this.$message.error(error.data.msg);
            }
          },
          async getScheduleList() {
            const res = await getTodaySchedule();
            const pending = res.data.data.pending.map((item) => ({
              ...item,
              isPending: true,
            }))
            const today = res.data.data.today.map((item) => ({
              ...item,
              isToday: true,
            }));
            const overdue = res.data.data.overdue.map((item) => ({
              ...item,
              isOverdue: true,
            }));
            this.scheduleList = pending.concat(today)
              .concat(overdue)
              .filter((item) => item.status === 0);
            this.scheduleCount = this.scheduleList.length;
            if (this.scheduleList.length > 0) {
              this.msgType = "schedule";
              this.messageVisible = this.scheduleList.some(
                (item) =>
                  (item.status === 0 &&
                    item.time < Math.ceil(new Date().getTime() / 1000)) || item.accept_status === 0
              );
            } else {
              this.msgType = "idcsmart";
            }
          },
          completeSchedule(item) {
            updateSchedule({ ...item, status: 1 }).then((res) => {
              this.$message.success(res.data.msg);
              this.getScheduleList();
            });
          },
          async getPlugin() {
            try {
              /* IdcsmartClientLevel */
              const res = await getCommonActivePlugin();
              const temp = res.data.data.list.reduce((all, cur) => {
                all.push(cur.name);
                return all;
              }, []);
              this.hasSchedule = temp.includes("Schedule");
              this.hasSchedule && this.getScheduleList();
            } catch (error) { }
          },
          readMsg(item) {
            apiNoticeRead({ ids: [item.id] })
              .then((res) => {
                this.$message.success(res.data.msg);
                item.read = 1;
              })
              .catch((err) => {
                this.$message.error(err.data.msg);
              });
          },
          // 获取官方通知
          syncNotice() {
            if (typeof apiNoticeSync !== "function") {
              return;
            }
            apiNoticeSync();
          },
          // 获取通用配置
          async getCommonSetting() {
            try {
              const res = await getCommon();
              localStorage.setItem("common_set", JSON.stringify(res.data.data));
              this.formData.clientTheme =
                res.data.data.clientarea_theme_color || "default";
            } catch (error) { }
          },
          /* 左侧全局搜索 */
          changeKey(key) {
            const temp = this.globalConfig
              .filter((item) => item.table === this.globalForm.table)[0]
              ?.field.filter((item) => item.key === key);
            this.curSearchType = temp[0]?.type;
            this.curSearchOptions = temp[0]?.option;
            this.globalForm.value = "";
          },
          async handleGlobal() {
            try {
              if (!this.globalForm.value) {
                return this.$message.warning(lang.please_improve_search);
              }
              this.searchLoading = true;
              const res = await sendGlobalSearch(this.globalForm);
              const data = res.data.data.list;
              const count = res.data.data.count;
              this.searchLoading = false;
              /* 跳转前处选中导航 id */
              const navList = JSON.parse(localStorage.getItem("backMenus"));
              let tempArr = navList.reduce((all, cur) => {
                cur.child && all.push(...cur.child);
                return all;
              }, []);
              const curValue = tempArr.filter(
                (item) => item.url === `${this.globalForm.table}.htm`
              )[0]?.id;
              localStorage.setItem("curValue", curValue);
              /* 跳转前处选中导航 id end */
              if (count === 1) {
                // 跳转详情
                const url =
                  this.globalForm.table === "order" ? "details" : "detail";
                location.href = `${this.baseUrl}${this.globalForm.table}_${url}.htm?id=${data[0].id}`;
              } else {
                location.href = `${this.baseUrl}${this.globalForm.table}.htm?type=${this.globalForm.key}&keywords=${this.globalForm.value}`;
              }
            } catch (error) {
              this.searchLoading = false;
              this.$message.error(error.data.msg);
            }
          },
          async getGlobalConfig() {
            try {
              const res = await getGlobalSearch();
              this.globalConfig = res.data.data.list || [];
              this.globalForm.table = this.globalConfig[0]?.table;
            } catch (error) { }
          },
          /* 左侧全局搜索 end */

          // 获取系统版本信息
          async getVersion() {
            try {
              if (
                sessionStorage.versionData &&
                JSON.parse(sessionStorage.versionData || {})?.updataTime &&
                new Date().getTime() -
                JSON.parse(sessionStorage.versionData || {})?.updataTime <
                1000 * 60 * 60 * 1
              ) {
                this.versionData = JSON.parse(sessionStorage.versionData);
                this.isCanUpdata = this.checkVersion(
                  this.versionData.version,
                  this.versionData.last_version
                );
              } else {
                const res = await version();
                const systemData = res.data.data;
                this.isCanUpdata = this.checkVersion(
                  systemData.version,
                  systemData.last_version
                );
                systemData.updataTime = new Date().getTime();
                this.versionData = systemData;
                sessionStorage.setItem(
                  "versionData",
                  JSON.stringify(this.versionData)
                );
              }
            } catch (error) { }
          },
          // 获取插件版本信息
          async getNewVersion() {
            try {
              if (
                sessionStorage.pluginUpgrade === "true" ||
                sessionStorage.pluginUpgrade === "false"
              ) {
                // 字符串转布尔值
                this.pluginUpgrade = sessionStorage.pluginUpgrade === "true";
              } else {
                const res = await getActiveVersion();
                this.pluginUpgrade = res.data.data.upgrade === 1;
              }
              sessionStorage.setItem("pluginUpgrade", this.pluginUpgrade);
            } catch (error) { }
          },
          checkVersion(nowStr, lastStr) {
            const nowArr = nowStr.split(".").map(Number);
            const lastArr = lastStr.split(".").map(Number);
            for (let i = 0; i < Math.max(nowArr.length, lastArr.length); i++) {
              const num1 = nowArr[i] || 0;
              const num2 = lastArr[i] || 0;
              if (num1 === num2) continue;
              return num1 < num2;
            }
            return false;
          },
          /**
           *
           * @param {string} nowStr 当前版本
           * @param {string} lastStr 最新版本
           */
          // checkVersion(nowStr, lastStr) {
          //   const nowArr = nowStr.split(".");
          //   const lastArr = lastStr.split(".");
          //   let hasUpdate = false;
          //   const nowLength = nowArr.length;
          //   const lastLength = lastArr.length;

          //   const length = Math.min(nowLength, lastLength);
          //   for (let i = 0; i < length; i++) {
          //     if (lastArr[i] - nowArr[i] > 0) {
          //       hasUpdate = true;
          //     }
          //   }
          //   if (!hasUpdate && lastLength - nowLength > 0) {
          //     hasUpdate = true;
          //   }
          //   return hasUpdate;
          // },
          setWebTitle() {
            const urlArr = location.pathname.split("/");
            const url =
              urlArr.length > 3
                ? urlArr.slice(2).join("/")
                : urlArr[urlArr.length - 1];
            const website_name = localStorage.getItem("back_website_name");
            const menu = JSON.parse(localStorage.getItem("backMenus"));
            let isSetTitle = false;
            menu.forEach((fir) => {
              const temp = fir.child || [];
              if (temp.length > 0) {
                let menu_id = "";
                if (
                  location.pathname.includes("client_") &&
                  !location.pathname.includes("idcsmart_client_") &&
                  !location.pathname.includes("client_care") &&
                  !location.pathname.includes("client_custom_field")
                ) {
                  menu_id = temp.filter((e) => e.url === "client.htm")[0]?.id;
                } else if (location.pathname.includes("supplier_")) {
                  menu_id = temp.filter((e) => e.url === "supplier_list.htm")[0]
                    ?.id;
                } else {
                  // menu_id = temp.filter(e => location.pathname.includes(e.url))[0]?.id
                }
                if (menu_id) {
                  localStorage.setItem("curValue", menu_id);
                  this.curValue = menu_id;
                }
                temp.forEach((sec) => {
                  if (sec.url === url) {
                    document.title =
                      (url === "index.htm" ? lang.home : sec.name) +
                      "-" +
                      website_name;
                    localStorage.lastTitle =
                      (url === "index.htm" ? lang.home : sec.name) +
                      "-" +
                      website_name;
                    isSetTitle = true;
                  } else if (
                    url.includes("plugin") &&
                    sec.url.split("/")[1] === url.split("/")[1] &&
                    !isSetTitle
                  ) {
                    // document.title = (url === 'index.htm' ? lang.home : sec.name) + '-' + website_name
                    if (localStorage.getItem("curValue") * 1 === sec.id * 1) {
                      document.title = sec.name + "-" + website_name;
                    }
                    localStorage.lastTitle =
                      (url === "index.htm" ? lang.home : sec.name) +
                      "-" +
                      website_name;
                  }
                });
              } else {
                if (fir.url === url) {
                  document.title =
                    (url === "index.htm" ? lang.home : fir.name) +
                    "-" +
                    website_name;
                } else if (
                  url.includes("plugin") &&
                  fir.url.split("/")[1] === url.split("/")[1]
                ) {
                  document.title =
                    (url === "index.htm" ? lang.home : fir.name) +
                    "-" +
                    website_name;
                } else {
                  document.title = localStorage.lastTitle || website_name;
                }
              }
            });
            /* 首页 */
            if (
              location.origin +
              "/" +
              location.pathname.split("/")[1] +
              "/index.htm" ===
              location.href
            ) {
              document.title = lang.homepage + "-" + website_name;
            }
          },
          async getSystemConfig() {
            try {
              const res = await getCommon();
              document.title = res.data.data.website_name;
              localStorage.lastTitle = res.data.data.website_name;
            } catch (error) {
              console.log(error);
            }
          },
          getAuth(auth) {
            return auth.map((item) => {
              item.child = item.child.filter((el) => el.url);
              return item;
            });
          },
          goHelp() {
            location.href = "http://doc.idcsmart.com/";
          },
          /*
           * 获取菜单url
           * @param {Number} id 菜单id 或者url
           * @return {String} url 菜单url
           */
          getMenuUrl(item) {
            return str + item.url || (item.child && str + item.child[0].url);
          },
          jumpHandler(e) {
            localStorage.setItem("curValue", e.id);
            if (
              e.url.includes("idcsmart_ticket") ||
              e.url.includes("idcsmart_ticket_internal") ||
              (e.child && str + e.child[0].url.includes("idcsmart_ticket")) ||
              (e.child &&
                str + e.child[0].url.includes("idcsmart_ticket_internal"))
            ) {
              this.audio_tip = new Audio(
                "/admin/template/default/media/tip.wav"
              );
              this.audio_tip.play();
              setTimeout(() => {
                this.audio_tip.pause();
                this.audio_tip = null;
              }, 2);
            }
            location.href = str + e.url || (e.child && str + e.child[0].url);
          },
          changeCollapsed() {
            this.collapsed = !this.collapsed;
            localStorage.setItem("customCollapsed", this.collapsed);
          },
          goIndex() {
            localStorage.setItem("curValue", 0);
            location.href = str + "index.htm";
          },
          changeSearch(e) {
            this.isSearchFocus = e;
            this.isShow = true;
            this.noData = false;
            if (this.timer) {
              clearTimeout(this.timer);
              this.timer = null;
            }
            this.timer = setTimeout(() => {
              this.globalSearchList();
            }, 500);
          },
          // 全局搜索
          async globalSearchList() {
            try {
              this.loadingSearch = true;
              const res = await globalSearch(this.isSearchFocus);
              this.global = res.data.data;
              if (
                this.global.clients.length === 0 &&
                this.global.products.length === 0 &&
                this.global.hosts.length === 0
              ) {
                this.noData = true;
              }
              this.loadingSearch = false;
            } catch (error) {
              console.log(error);
              this.loadingSearch = false;
            }
          },
          changeSearchFocus(value) {
            if (value) {
              if (this.global) {
                this.isShow = true;
              }
            }
            this.isSearchFocus = value;
          },
          // 个人中心
          handleNav() { },
          // 退出登录
          async handleLogout() {
            try {
              const res = await Axios.post("/logout");
              this.$message.success(res.data.msg);
              localStorage.removeItem("backJwt");
              setTimeout(() => {
                const host = location.origin;
                const fir = location.pathname.split("/")[1];
                const str = `${host}/${fir}/`;
                location.href = str + "login.htm";
              }, 300);
            } catch (error) {
              this.$message.error(error.data.msg);
            }
          },
          // 语言切换
          async changeLang(e) {
            try {
              const index = this.langList.findIndex(
                (item) => item.display_lang === e.value
              );
              if (
                localStorage.getItem("backLang") !== e.value ||
                !localStorage.getItem("backLang")
              ) {
                localStorage.setItem(
                  "country_imgUrl",
                  this.langList[index].display_img
                );
                localStorage.setItem("backLang", e.value);
              }
              // 更新系统设置里面的后台语言
              // 先获取后更改
              const res = await getSystemOpt();
              const params = res.data.data;
              params.lang_admin = e.value;
              await updateSystemOpt(params);
              // 获取导航
              const menus = await getMenus();
              const menulist = menus.data.data.menu;
              localStorage.setItem("backMenus", JSON.stringify(menulist));
              window.location.reload();
            } catch (error) {
              console.log(error);
            }
          },
          // 颜色配置
          toUnderline(name) {
            return name.replace(/([A-Z])/g, "_$1").toUpperCase();
          },
          changeClientTheme(theme) {
            updateClientTheme({
              clientarea_theme_color: theme,
            })
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(res.data.msg);
                }
              })
              .catch((err) => {
                this.formData.clientTheme =
                  JSON.parse(localStorage.getItem("common_set"))
                    ?.clientarea_theme_color || "default";
                this.$message.error(err.data.msg);
              });
          },
          getBrandColor(type, colorList) {
            const name = /^#[A-F\d]{6}$/i.test(type)
              ? type
              : this.toUnderline(type);
            return colorList[name || "DEFAULT"];
          },
          /* 页面配置 */
          toggleSettingPanel() {
            this.visible = true;
          },
          toSafeCenter() {
            location.href = str + "security_center.htm";
          },
          handleClick() {
            this.visible = true;
          },
          getModeIcon(mode) {
            if (mode === "light") {
              return SettingLightIcon;
            }
            if (mode === "dark") {
              return SettingDarkIcon;
            }
            return SettingAutoIcon;
          },
          // 主题
          onPopupVisibleChange(visible, context) {
            if (!visible && context.trigger === "document")
              this.isColoPickerDisplay = visible;
          },

          // 修改密码相关
          // 关闭修改密码弹窗
          editPassClose() {
            this.editPassVisible = false;
            this.editPassFormData = {
              password: "",
              repassword: "",
            };
          },
          // 修改密码提交
          onSubmit({ validateResult, firstError }) {
            if (validateResult === true) {
              const params = {
                password: this.editPassFormData.password,
                repassword: this.editPassFormData.repassword,
              };
              editPass(params)
                .then((res) => {
                  if (res.data.status === 200) {
                    this.editPassClose();
                    this.$message.success(res.data.msg);
                    this.handleLogout();
                  }
                })
                .catch((error) => {
                  this.$message.error(error.data.msg);
                });
              console.log(this.editPassFormData);
            } else {
              console.log("Errors: ", validateResult);
            }
          },
          // 确认密码检查
          checkPwd(val) {
            if (val !== this.editPassFormData.password) {
              return {
                result: false,
                message: window.lang.password_tip,
                type: "error",
              };
            }
            return { result: true };
          },
        },
        watch: {
          "formData.mode"() {
            if (this.formData.mode === "auto") {
              document.documentElement.setAttribute("theme-mode", "");
            } else {
              document.documentElement.setAttribute(
                "theme-mode",
                this.formData.mode
              );
            }
            localStorage.setItem("theme-mode", this.formData.mode);
          },
          "formData.brandTheme"() {
            document.documentElement.setAttribute(
              "theme-color",
              this.formData.brandTheme
            );
            localStorage.setItem("theme-color", this.formData.brandTheme);
          },
        },
      }).$mount(aside);
    }

    /* footer */
    if (footer) {
      new Vue({
        data() {
          return {};
        },
      }).$mount(footer);
    }

    var loading = document.getElementById("loading");
    setTimeout(() => {
      loading && (loading.style.display = "none");
    }, 200);
    typeof old_onload == "function" && old_onload();
  };
})(window);

const mixin = {
  data() {
    return {
      addonArr: [], // 已激活的插件
    };
  },
  methods: {
    async getAddonList() {
      try {
        const res = await getCommonActivePlugin();
        this.addonArr = res.data.data.list.map((item) => item.name);
      } catch (error) { }
    },
  },
  created() {
    this.getAddonList();
  },
};

const fixedFooter = {
  data() {
    return {
      footerInView: false,
      footerObserver: null,
    };
  },
  methods: {
    checkFooterPosition(entries) {
      this.footerInView = entries[0].intersectionRatio > 0;
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.footerObserver = new IntersectionObserver(this.checkFooterPosition);
      this.footerObserver.observe(document.getElementById("footer"));
    });
  },
  beforeDestroy() {
    this.footerObserver && this.footerObserver.disconnect();
  },
};

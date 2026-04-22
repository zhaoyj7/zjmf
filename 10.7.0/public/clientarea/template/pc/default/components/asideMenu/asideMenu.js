// css 样式依赖common.css
const asideMenu = {
  template: `
<el-aside width="190px">
      <a :href="commonData.clientarea_logo_url || '/home.htm'" onclick="return false" class="menu-alink">
        <img class="ali-logo" :src="logo" @click="goHome" v-show="logo"></img>
      </a>
    <el-menu class="menu-top" :default-active="menuActiveId" @select="handleSelect" background-color="transparent"
        text-color="var(--color-menu-text)" active-text-color="var(--color-menu-text-active)">
        <template v-for="(item,index) in menu1">
            <!-- 只有一级菜单 -->
            <template v-if="!item.child || item.child?.length === 0">
                <a :href="getMenuUrl(item.id || item.url)" onclick="return false" class="menu-alink">
                    <el-menu-item :key="item.id ? item.id : item.url" :index="item.id ? item.id + '' : item.url">
                        <i class="iconfont" :class="item.icon"></i>
                        <span class="aside-menu-text" slot="title">{{item.name}}</span>
                    </el-menu-item>
                </a>
            </template>

            <!-- 有二级菜单 -->
            <el-submenu v-else :key="item.id ? item.id : item.url" :index="item.id ? item.id + '' : item.url">
                <template slot="title">
                    <i class="iconfont" :class="item.icon"></i>
                    <span class="aside-menu-text" slot="title">{{item.name}}</span>
                </template>
                <template v-for="child in item.child">
                    <a :href="getMenuUrl(child.id || child.url)" onclick="return false" class="menu-alink">
                        <el-menu-item :index="child.id + ''" :key="child.id">
                            {{child.name}}
                        </el-menu-item>
                    </a>
                </template>
            </el-submenu>
        </template>
    </el-menu>
    <div class="line" v-if="hasSeparate"></div>
    <el-menu class="menu-top" :default-active="menuActiveId" @select="handleSelect" background-color="transparent"
        text-color="var(--color-menu-text)" active-text-color="#FFF">
        <template v-for="(item,index) in menu2">
            <!-- 只有一级菜单 -->
            <template v-if="!item.child || item.child?.length === 0">
                <a :href="getMenuUrl(item.id || item.url)" onclick="return false" class="menu-alink">
                    <el-menu-item :key="item.id ? item.id + '' : item.url" :index="item.id ? item.id + '' : item.url">
                        <i class="iconfont" :class="item.icon"></i>
                        <span class="aside-menu-text" slot="title">{{item.name}}</span>
                    </el-menu-item>
                </a>
            </template>

            <!-- 有二级菜单 -->
            <el-submenu v-else :key="item.id ? item.id : item.url" :index="item.id ? item.id + '' : item.url">
                <template slot="title">
                    <i class="iconfont" :class="item.icon"></i>
                    <span class="aside-menu-text" slot="title">{{item.name}}</span>
                </template>
                <template v-for="child in item.child">
                    <a :href="getMenuUrl(child.id || child.url)" onclick="return false" class="menu-alink">
                        <el-menu-item :index="child.id + ''" :key="child.id">
                            {{child.name}}
                        </el-menu-item>
                    </a>
                </template>
            </el-submenu>
        </template>
    </el-menu>
</el-aside>

  `,
  // 云服务器 当前
  // 物理服务器 dcim
  // 通用产品
  data() {
    return {
      activeId: 1,
      menu1: [],
      menu2: [],
      logo: "",
      menuActiveId: "",
      iconsData: [],
      commonData: {},
      noRepeat: [],
      hasSeparate: false,
      originMenu: [],
    };
  },
  mounted() {
    this.doGetMenu();
    // const mainLoading = document.getElementById("mainLoading");
    // if (mainLoading) {
    //   mainLoading.style.display = "none";
    // }
    // if (document.getElementsByClassName("template")[0]) {
    //   document.getElementsByClassName("template")[0].style.display = "block";
    // }
  },
  created() {
    this.getCommonSetting();
  },
  beforeUpdate() {},
  mixins: [mixin],
  updated() {
    // // 关闭loading
    // document.getElementsByClassName('template')[0].style.display = 'block'
    const mainLoading = document.getElementById("mainLoading");
    if (mainLoading) {
      mainLoading.style.display = "none";
    }
    if (document.getElementsByClassName("template")[0]) {
      document.getElementsByClassName("template")[0].style.display = "block";
    }
  },
  methods: {
    // 页面跳转
    // toPage(e) {
    //     // 获取 当前点击导航的id 存入本地
    //     const id = e.id
    //     localStorage.setItem('frontMenusActiveId', id)
    //     // 跳转到对应路径
    //     location.href = '/' + e.url
    // },
    // 获取通用配置
    async getCommonSetting() {
      try {
        const res = await getCommon();
        this.commonData = res.data.data;
        localStorage.setItem(
          "common_set_before",
          JSON.stringify(res.data.data)
        );
        this.logo = this.commonData.system_logo;
      } catch (error) {}
    },
    // 判断当前菜单激活
    setActiveMenu() {
      const originUrl = location.pathname.slice(1);
      const allUrl = originUrl + location.search;
      let flag = false;
      this.originMenu.forEach((item) => {
        // 当前url下存在和导航菜单对应的路径
        if (!item.child && item.url) {
          const url = String(item.url).split("?");
          if (
            (url.length > 1 && item.url == allUrl) ||
            (url.length == 1 && item.url == originUrl)
          ) {
            this.menuActiveId = item.id + "";
            flag = true;
          }
        }
        // 当前url下存在二级菜单
        if (item.child && item.child.length > 0) {
          item.child.forEach((child) => {
            const url = String(child.url).split("?");
            if (
              (url.length > 1 && child.url == allUrl) ||
              (url.length == 1 && child.url == originUrl)
            ) {
              this.menuActiveId = child.id + "";
              flag = true;
            }
          });
        }
      });
      if (!flag) {
        this.menuActiveId = localStorage.getItem("frontMenusActiveId") || "";
      }
    },
    goHome() {
      localStorage.frontMenusActiveId = "";
      const openUrl = this.commonData.clientarea_logo_url || "/home.htm";
      if (this.commonData.clientarea_logo_url_blank == 1) {
        window.open(openUrl);
      } else {
        location.href = openUrl;
      }
    },

    // 获取前台导航
    doGetMenu() {
      getMenu().then((res) => {
        if (res.data.status === 200) {
          res.data.data.menu.forEach((item) => {
            if (item.child && item.child.length > 0) {
              this.originMenu.push(...item.child);
            } else {
              this.originMenu.push(item);
            }
          });
          const menu = res.data.data.menu;
          localStorage.setItem("frontMenus", JSON.stringify(menu));
          let index = menu.findIndex((item) => item.name == "分隔符");
          if (index != -1) {
            this.hasSeparate = true;
            this.menu1 = menu.slice(0, index);
            this.menu2 = menu.slice(index + 1);
          } else {
            this.hasSeparate = false;
            this.menu1 = menu;
          }

          this.setActiveMenu();
        }
      });
      // 获取详情
      accountDetail()
        .then((res) => {
          if (res.data.status == 200) {
            let obj = res.data.data.account;
            let id = res.data.data.account.id;
            localStorage.setItem(
              "is_sub_account",
              obj.customfield?.is_sub_account
            );
            if (obj.customfield?.is_sub_account == 1) {
              // 子账户
              accountPermissions(id).then((relust) => {
                let rule = relust.data.data.rule;
                this.$emit("getruleslist", rule);
              });
            } else {
              // 主账户
              this.$emit("getruleslist", "all");
            }
          }
        })
        .catch((err) => {
          console.log(err, "err----->");
        });
    },
    arrFun(n) {
      for (var i = 0; i < n.length; i++) {
        //用typeof判断是否是数组
        if (n[i].child && typeof n[i].child == "object") {
          let obj = JSON.parse(JSON.stringify(n[i]));
          delete obj.child;
          this.noRepeat.push(obj);
          this.arrFun(n[i].child);
        } else {
          this.noRepeat.push(n[i]);
        }
      }
    },

    /*
     * 获取菜单url
     * @param {Number} id 菜单id 或者url
     * @return {String} url 菜单url
     */
    getMenuUrl(id) {
      const temp =
        this.originMenu.find((item) => item.id == id || item.url == id) || {};
      const reg =
        /^(((ht|f)tps?):\/\/)([^!@#$%^&*?.\s-]([^!@#$%^&*?.\s]{0,63}[^!@#$%^&*?.\s])?\.)+[a-z]{2,6}\/?/;
      let url = "/" + temp.url;
      if (reg.test(temp.url)) {
        if (temp?.second_reminder === 1) {
          url = `/transfer.htm?target=${encodeURIComponent(temp.url)}`;
        } else {
          url = temp.url;
        }
      }
      return url;
    },

    handleSelect(id) {
      localStorage.setItem("frontMenusActiveId", id);
      const temp =
        this.originMenu.find((item) => item.id == id || item.url == id) || {};
      const reg =
        /^(((ht|f)tps?):\/\/)([^!@#$%^&*?.\s-]([^!@#$%^&*?.\s]{0,63}[^!@#$%^&*?.\s])?\.)+[a-z]{2,6}\/?/;
      if (reg.test(temp.url)) {
        if (temp?.second_reminder === 1) {
          return window.open(
            `/transfer.htm?target=${encodeURIComponent(temp.url)}`
          );
        } else {
          return window.open(temp.url);
        }
      }
      location.href = "/" + temp.url;
    },
    getAllIcon() {
      let url = "/upload/common/iconfont/iconfont.json";
      let _this = this;

      // 申明一个XMLHttpRequest
      let request = new XMLHttpRequest();
      // 设置请求方法与路径
      request.open("get", url);
      // 不发送数据到服务器
      request.send(null);
      //XHR对象获取到返回信息后执行
      request.onload = function () {
        // 解析获取到的数据
        let data = JSON.parse(request.responseText);
        _this.iconsData = data.glyphs;
        _this.iconsData.map((item) => {
          item.font_class = "icon-" + item.font_class;
        });
      };
    },
  },
};

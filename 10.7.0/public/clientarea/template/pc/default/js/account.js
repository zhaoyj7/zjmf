(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("template")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        asideMenu,
        topMenu,
        countDownButton,
        pagination,
        captchaDialog,
        securityVerification,
      },
      directives: {
        plugin: {
          inserted: function (el, binding) {
            const addonsDom = document.querySelector("#addons_js");
            let addonsArr = [];
            let arr = [];
            if (addonsDom) {
              addonsArr = JSON.parse(addonsDom.getAttribute("addons_js")) || []; // 插件列表
              // 判断是否安装了某插件
              arr = addonsArr.filter((item) => item.name === binding.value);
              if (arr.length === 0) {
                // 未安装 移除该元素的dom
                el.parentNode.removeChild(el);
              }
            } else {
              el.parentNode.removeChild(el);
            }
          },
        },
      },
      created() {
        this.getCommonData();
        this.getCountry();
        this.getWxConectInfo();
      },
      mounted() {
        window.addEventListener("scroll", this.computeScroll);
        const addons = document.querySelector("#addons_js");
        this.addons_js_arr = JSON.parse(addons.getAttribute("addons_js"));
        this.getCertificationInfo();
        this.hasWxPlugin = havePlugin("MpWeixinNotice");
        havePlugin("ClientCustomField") && this.getClientCustomFieldValue();
      },
      updated() {
        // 关闭loading
        document.getElementById("mainLoading").style.display = "none";
        document.getElementsByClassName("template")[0].style.display = "block";
      },
      destroyed() {
        window.removeEventListener("scroll", this.computeScroll);
      },
      data() {
        var validatePass2 = (rule, value, callback) => {
          if (value === "") {
            callback(new Error(lang.account_tips48));
          } else if (value !== this.operateData.operate_password) {
            callback(new Error(lang.account_tips31));
          } else {
            callback();
          }
        };
        return {
          idcsmart_client_level: {
            name: "",
            id: "",
            background_color: "",
          },
          showAccountController: false, // 是否展示概要
          showLogController: false, // 是否展示查看日志
          addons_js_arr: [],
          isShowCaptcha: false, //是否显示验证码弹窗
          tip_dialong_show: false,
          isShowPeratedia: false,
          operateData: {
            operate_password: "",
            re_operate_password: "",
          },
          subLoading: false,
          PermissionsList: [], // 权限列表
          activeIndex: "1",
          loopTimer: null,
          clientCustomFieldList: [],
          // 账户姓名
          userName: "",
          // 账户国家图片
          curSrc: "",
          // 获取的账户信息
          accountData: {},
          // 原始账户信息
          orginAcountData: {},
          oauth: [],
          origin_language: "",
          activeType: "",
          codeAction: "phoneCode",
          // 国家列表
          countryList: [],
          // 认证状态相关信息对象
          attestationStatusInfo: {
            iocnShow: false, // 认证信息是否显示
            iconUrl: null, // 图标
            text: "", // 文字信息
            status: 0, // 认证状态  0：未认证 10：仅个人认证通过  20：仅企业认证通过：30：个人企业均认证通过 40:失败
            certification_company_open: 0,
          },
          certification_open: 0, // 认证是否开启
          isShowPass: false,
          passData: {
            old_password: "",
            new_password: "",
            repassword: "",
          },
          imgShow: false,
          phoneData: {},
          rePhoneData: {
            countryCode: 86,
          },
          emailData: {},
          reEmailData: {},
          isShowPhone: false,
          isShowRePhone: false,
          isShowEmail: false,
          isShowReEmail: false,
          isShowCodePass: false,
          isEmailOrPhone: true,
          prohibit_user_information_changes: [],
          // 图形验证码
          token: "",
          captcha: "",
          // 操作日志相关
          loading: false,
          dataList: [],
          params: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 200,
            orderby: "id",
            sort: "desc",
            keywords: "",
          },

          timerId: null,
          rules: {},
          ruleForm: {},
          // 忘记密码相关
          formData: {
            email: "",
            phone: "",
            password: "",
            repassword: "",
            phoneCode: "",
            emailCode: "",
            countryCode: 86,
          },
          operaRules: {
            origin_operate_password: [
              {required: true, message: lang.account_tips1, trigger: "blur"},
            ],
            operate_password: [
              {required: true, message: lang.account_tips2, trigger: "blur"},
            ],
            re_operate_password: [
              {required: true, message: lang.account_tips3, trigger: "blur"},
              {validator: validatePass2, trigger: "blur"},
            ],
          },
          errorText: "",
          commonData: {},
          isShowBackTop: false,
          scrollY: 0,
          isEnd: false,
          isShowMore: false,
          msgDataList: [],
          msgCount: 0,
          tipsArr: [],
          isTips: false,
          msgParams: {
            page: 1,
            limit: 20,
            pageSizes: [20, 50, 100],
            total: 0,
            orderby: "id",
            sort: "desc",
            keywords: "",
            type: "",
            read: "",
          },
          msgLoading: false,
          saveLoading: false,
          multipleSelection: [],
          msgType: {
            official: lang.subaccount_text54,
            host: lang.finance_info,
            finance: lang.finance_text123,
          },
          options: [
            {
              value: "",
              label: lang.subaccount_text65,
            },
            {
              value: 1,
              label: lang.subaccount_text66,
            },
            {
              value: 0,
              label: lang.subaccount_text67,
            },
          ],
          msgTypeOptions: [
            {
              value: "official",
              label: lang.subaccount_text54,
            },
            {
              value: "host",
              label: lang.finance_info,
            },
            {
              value: "finance",
              label: lang.finance_text123,
            },
          ],
          hasWxPlugin: false,
          conectInfo: {
            is_subscribe: 0,
            accept_push: 0,
          },
          isNextPageDisabled: false,
          security_verify_method: "", // 安全验证方式
          security_verify_value: "", // 安全验证值
          certify_id: "", // 认证ID
          actionType: "",
        };
      },
      watch: {},
      computed: {},
      filters: {
        formateTime(time) {
          if (time && time !== 0) {
            return formateDate(time * 1000);
          } else {
            return "--";
          }
        },
      },
      methods: {
        handleChange(type) {
          if (type === 1) {
            this.params.page += 1;
          } else {
            if (this.params.page > 1) {
              this.params.page -= 1;
            }
          }
          this.getAccountList();
        },
        async changeWxPush(val) {
          try {
            const res = await changePushStatus({
              status: val,
            });
            this.$message.success(res.data.msg);
            this.getWxConectInfo();
          } catch (error) {
            this.$message.error(error.data.msg);
            this.getWxConectInfo();
          }
        },
        async getWxConectInfo() {
          try {
            const res = await getWxInfo();
            this.conectInfo = res.data.data;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        // 验证码验证成功后的回调
        getData(captchaCode, token) {
          this.token = token;
          this.captcha = captchaCode;
          this.isShowCaptcha = false;
          if (this.codeAction === "emailCode") {
            this.sendEmailCode(this.activeType);
          } else if (this.codeAction === "phoneCode") {
            this.sendPhoneCode(this.activeType);
          }
        },
        // 验证码 关闭
        captchaCancel() {
          this.isShowCaptcha = false;
        },
        handleSelectionChange(val) {
          this.multipleSelection = val;
        },
        handelDelMsg() {
          if (this.multipleSelection.length === 0) {
            this.$message.warning(lang.subaccount_text68);
            return;
          }
          let ids = this.multipleSelection.map((item) => item.id);
          deleteMessage({id: ids}).then((res) => {
            if (res.data.status === 200) {
              this.$message.success(res.data.msg);
              this.getMsgList();
            }
          });
        },
        havePlugin(pluginName) {
          const addonsDom = document.querySelector("#addons_js");
          let addonsArr = [];
          let arr = [];
          if (addonsDom) {
            addonsArr = JSON.parse(addonsDom.getAttribute("addons_js")); // 插件列表
            arr = addonsArr.map((item) => {
              return item.name;
            });
          }
          return arr.includes(pluginName);
        },
        goMsgDetail(id) {
          sessionStorage.setItem("msgParams", JSON.stringify(this.msgParams));
          window.open(
            `/plugin/${getPluginId("ClientCare")}/msgDetail.htm?id=${id}`
          );
        },
        handelReadMsg() {
          if (this.multipleSelection.length === 0) {
            this.$message.warning(lang.subaccount_text68);
            return;
          }
          let ids = this.multipleSelection.map((item) => item.id);
          readMessage({id: ids}).then((res) => {
            if (res.data.status === 200) {
              this.$refs.topMenuRef.getMessageList();
              this.$message.success(res.data.msg);
              this.getMsgList();
            }
          });
        },
        handelReadAllMsg() {
          readMessage({all: 1}).then((res) => {
            if (res.data.status === 200) {
              this.$message.success(res.data.msg);
              this.$refs.topMenuRef.getMessageList();
              this.getMsgList();
            }
          });
        },
        getRule(arr) {
          let isShow1 = this.showFun(arr, "AccountController::index");
          let isShow2 = this.showFun(arr, "LogController::list");
          if (isShow2) {
            this.showLogController = true;
            this.activeIndex = "2";
          }
          if (isShow1) {
            this.showAccountController = true;
            this.activeIndex = "1";
          }
          // 如果地址栏有参数
          if (location.search) {
            let params = location.search.split("?")[1].split("&");
            let obj = {};
            params.forEach((item) => {
              let arr = item.split("=");
              obj[arr[0]] = arr[1];
            });
            if (obj.type) {
              this.activeIndex = obj.type;
            }
          }
          if (sessionStorage.msgParams) {
            let params = JSON.parse(sessionStorage.msgParams);
            this.msgParams = params;
            this.activeIndex = "3";
            sessionStorage.removeItem("msgParams");
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
        // tab 切换
        handleClick() {
          if (this.activeIndex === "1") {
            this.getAccount();
          } else if (this.activeIndex === "2") {
            this.getAccountList();
          } else if (this.activeIndex === "3") {
            this.getMsgList();
          }
        },
        async getMsgList() {
          this.msgLoading = true;
          await messageList(this.msgParams).then((res) => {
            if (res.data.status === 200) {
              this.msgTypeOptions = res.data.data.type;
              this.msgType = this.msgTypeOptions.reduce((all, cur) => {
                all[cur.name] = cur.name_lang;
                return all;
              }, {});
              this.msgDataList = res.data.data.list;
              this.msgParams.total = res.data.data.count;
            }
          });
          this.msgLoading = false;
        },
        msgSizeChange(e) {
          this.msgParams.limit = e;
          this.msgParams.page = 1;
          this.getMsgList();
        },
        msgCurrentChange(e) {
          this.msgParams.page = e;
          this.getMsgList();
        },
        clearKey() {
          this.msgParams.keywords = "";
          this.msgInputChange();
        },
        // 搜索框
        msgInputChange() {
          this.msgParams.page = 1;
          this.getMsgList();
        },
        sizeChange(e) {
          this.params.limit = e;
          this.params.page = 1;
          this.getAccountList();
        },
        currentChange(e) {
          this.params.page = e;
          this.getAccountList();
        },
        // 获取账户操作日志
        getAccountList() {
          // 表格加载
          this.loading = true;
          getLog({...this.params, type: "system"}).then((res) => {
            if (res.data.status === 200) {
              let list = res.data.data.list;
              this.dataList = list;
              this.params.total = res.data.data.count;
            }
            this.loading = false;
          });
        },
        // 搜索框
        inputChange() {
          this.params.page = 1;
          this.getAccountList();
        },
        cancelOauth(item) {
          cancelOauth(item.name)
            .then((res) => {
              this.$message.success(res.data.msg);
              this.getAccount();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        },
        bingOauth(item) {
          oauthUrl(item.name).then((res) => {
            const openWindow = window.open(
              res.data.data.url,
              "oauth",
              "width=800,height=800"
            );
            clearInterval(this.loopTimer);
            this.loopTimer = null;
            this.loopTimer = setInterval(() => {
              if (openWindow.closed) {
                clearInterval(this.loopTimer);
                this.loopTimer = null;
                this.getAccount();
              }
            }, 300);
          });
        },
        // 打开验证码修改密码弹窗
        showCodePass() {
          this.security_verify_method = "";
          this.security_verify_value = "";
          this.certify_id = "";
          this.formData = {
            email: this.accountData.email,
            phone: this.accountData.phone,
            password: "",
            repassword: "",
            phoneCode: "",
            emailCode: "",
            countryCode: this.accountData.phone_code || 86,
          };
          this.isShowPass = false;
          this.isShowCodePass = true;
          this.errorText = "";
        },
        // 关闭验证码修改密码弹窗
        quiteCodePass() {
          this.isShowCodePass = false;
        },
        openPreaDia() {
          this.security_verify_method = "";
          this.security_verify_value = "";
          this.certify_id = "";
          this.isShowPeratedia = true;
        },
        noticeChange(val) {
          if (val && !this.accountData.notice_method) {
            this.accountData.notice_method = "all";
          }
        },
        // 获取账户详情
        getAccount() {
          account().then((res) => {
            this.imgShow = false;
            if (res.data.status === 200) {
              this.accountData = res.data.data.account;
              this.orginAcountData = res.data.data.account;
              this.idcsmart_client_level =
                res.data.data.account.customfield?.idcsmart_client_level || {};
              this.oauth =
                res.data.data.account.oauth.map((item) => {
                  item.showStatus = item.link
                    ? lang.oauth_text6
                    : lang.oauth_text7;
                  return item;
                }) || [];
              this.userName = res.data.data.account.username;
              this.commonData = JSON.parse(
                localStorage.getItem("common_set_before")
              );

              if (!this.accountData.language) {
                this.accountData.language = this.commonData.lang_home;
              }
              this.origin_language = this.accountData.language;
              localStorage.setItem("lang", this.origin_language);

              const safe_method = this.commonData.home_enforce_safe_method;

              if (
                !res.data.data.account.set_operate_password &&
                safe_method.includes("operate_password")
              ) {
                this.tipsArr.push(lang.account_tips_text3);
              }
              if (
                !res.data.data.account.phone &&
                safe_method.includes("phone")
              ) {
                this.tipsArr.push(lang.account_label15);
              }
              if (
                !res.data.data.account.email &&
                safe_method.includes("email")
              ) {
                this.tipsArr.push(lang.account_label14);
              }
              if (
                safe_method.includes("oauth") &&
                res.data.data.account.oauth.length > 0 &&
                res.data.data.account.oauth.filter((item) => item.link === true)
                  .length === 0
              ) {
                this.tipsArr.push(lang.oauth_text5);
              }
              this.showTips();
              // 如果账户选择了国家
              // 掉接口查找国家的 iso 图片前缀 进行拼接
              if (this.accountData.country) {
                const params = {
                  keywords: this.accountData.country,
                };
                country(params).then((res) => {
                  if (res.data.status === 200) {
                    const iso = res.data.data.list[0].iso;
                    this.curSrc = `/upload/common/country/${iso}.png`;
                    this.imgShow = true;
                  }
                });
              }
            }
          });
        },
        showTips() {
          if (this.tipsArr.length > 0) {
            this.$message.warning(
              lang.account_tips_text6 + this.tipsArr.join("、")
            );
            this.tipsArr = [];
          }
        },
        // 获取认证状态信息
        getCertificationInfo() {
          if (havePlugin("IdcsmartCertification")) {
            certificationInfo().then((ress) => {
              this.certification_open = ress?.data?.data?.certification_open;
              this.attestationStatusInfo.iocnShow = false;
              if (ress.data.status === 200) {
                this.attestationStatusInfo.iocnShow = true;
                this.attestationStatusInfo.certification_company_open =
                  ress.data.data.certification_company_open;
                const companyStatus = ress.data.data.company.status;
                const personStatus = ress.data.data.person.status;
                // 认证失败
                if (
                  !ress.data.data.is_certification ||
                  (companyStatus !== 1 && personStatus !== 1)
                ) {
                  const safe_method = this.commonData.home_enforce_safe_method;
                  if (safe_method.includes("certification")) {
                    this.tipsArr.push(lang.account_tips21);
                    this.showTips();
                  }
                  this.attestationStatusInfo.iconUrl = `${url}/img/account/unauthorized.png`;
                  this.attestationStatusInfo.text = window.lang.account_tips12;
                  if (companyStatus === 3 || companyStatus === 4) {
                    // 企业资料审核中
                    this.attestationStatusInfo.status = 25;
                  } else if (personStatus === 3 || personStatus === 4) {
                    // 个人资料审核中
                    this.attestationStatusInfo.status = 15;
                  } else if (companyStatus === 2 || personStatus === 2) {
                    if (companyStatus === 2) {
                      // 企业认证失败
                      this.attestationStatusInfo.status = 40;
                    } else {
                      // 个人认证失败
                      this.attestationStatusInfo.status = 45;
                    }
                  } else {
                    this.attestationStatusInfo.status = 0; // 未认证
                  }
                  return;
                } else if (companyStatus === 1) {
                  // 企业认证成功
                  this.attestationStatusInfo.iconUrl = `${url}/img/account/enterprise_certification.png`;
                  this.attestationStatusInfo.text = window.lang.account_tips13;
                  if (personStatus === 1) {
                    this.attestationStatusInfo.status = 30; // 个人成功
                  } else {
                    this.attestationStatusInfo.status = 20; // 个人未成功
                  }
                  return;
                } else if (personStatus === 1) {
                  // 个人认证成功
                  this.attestationStatusInfo.iconUrl = `${url}/img/account/personal_certification.png`;
                  this.attestationStatusInfo.text = window.lang.account_tips14;
                  if (companyStatus === 1) {
                    this.attestationStatusInfo.status = 30;
                  } else if (companyStatus === 2 || companyStatus === 3) {
                    // 个人成功 企业审核中
                    this.attestationStatusInfo.status = 26;
                  } else {
                    this.attestationStatusInfo.status = 10;
                  }
                  return;
                }
              }
            });
          }
        },
        // 点击认证图标
        handelAttestation() {
          const plugin_id = getPluginId("IdcsmartCertification");
          // 未认证或者都未通过时 跳转认证选择页面
          if (this.attestationStatusInfo.status === 0) {
            location.href = `plugin/${plugin_id}/authentication_select.htm`;
            return;
          }
          const type2Status = [20, 25, 26, 30, 40]; // 20:企业成功，个人未成功   25:企业审核中  26:个人成功 企业审核中  30:企业个人都成功  40:企业认证失败
          const type1Status = [15, 45]; // 15:个人审核中  45:个人认证失败
          if (type2Status.includes(this.attestationStatusInfo.status)) {
            location.href = `plugin/${plugin_id}/authentication_status.htm?type=2`;
            return;
          } else if (type1Status.includes(this.attestationStatusInfo.status)) {
            location.href = `plugin/${plugin_id}/authentication_status.htm?type=1`;
            return;
          } else if (
            this.attestationStatusInfo.status === 10 &&
            this.attestationStatusInfo.certification_company_open === 1
          ) {
            // 仅个人认证成功时 跳转个人认证成功页面
            location.href = `plugin/${plugin_id}/authentication_status.htm?type=3`;
            return;
          }
        },
        calcValidator(item, value, callback, regexpr) {
          if (
            (item.required === 1 || item.before_settle === 1) &&
            value === ""
          ) {
            callback(new Error(lang.custom_goods_text1));
            return;
          }
          if (
            value !== "" &&
            !new RegExp(regexpr.replace(/^\/|\/$/g, "")).test(value)
          ) {
            callback(new Error(lang.custom_goods_text2));
            return;
          }
          callback();
        },

        calcRules(item) {
          const rules = [];
          if (item.required === 1 || item.before_settle === 1) {
            rules.push({
              required: true,
              message: lang.custom_goods_text1,
              trigger: ["blur", "change"],
            });
          } else {
            rules.push({
              required: false,
              trigger: ["blur", "change"],
            });
          }

          if (item.type === "link") {
            // 类型为链接时需要校验url格式 http://www.baidu.com
            const url =
              "/^(((ht|f)tps?)://)?([^!@#$%^&*?.s-]([^!@#$%^&*?.s]{0,63}[^!@#$%^&*?.s])?.)+[a-z]{2,6}/?/";
            rules.push({
              validator: (rule, value, callback) =>
                this.calcValidator(item, value, callback, url),
              trigger: ["blur", "change"],
            });
          }
          if (
            item.type !== "dropdown" &&
            item.type !== "tickbox" &&
            item.regexpr
          ) {
            rules.push({
              validator: (rule, value, callback) =>
                this.calcValidator(item, value, callback, item.regexpr),
              trigger: ["blur", "change"],
            });
          }
          return rules;
        },
        getClientCustomFieldValue() {
          clientCustomFieldValue().then((res) => {
            const obj = {};
            const rules = {};
            this.clientCustomFieldList = res.data.data.list.map((item) => {
              obj[item.id + ""] = item.value;
              rules[item.id + ""] = this.calcRules(item);
              if (item.type === "dropdown_text") {
                item.select_select = item.value.split("|")[0];
                obj[item.id + ""] = item.value.split("|")[1];
              }
              return item;
            });
            this.$set(this, "ruleForm", obj);
            this.$set(this, "rules", rules);
            if (
              this.commonData.custom_fields &&
              this.commonData.custom_fields.before_settle === 1 &&
              window.opener &&
              window.opener !== window
            ) {
              this.$message.warning(lang.buy_tip_text);
              setTimeout(() => {
                this.$refs.ruleForm.validate();
              }, 10);
            }
          });
        },
        // 获取国家列表
        getCountry() {
          country().then((res) => {
            if (res.data.status === 200) {
              this.countryList = res.data.data.list;
            }
          });
        },
        // 编辑基础资料
        saveAccount() {
          this.$refs.ruleForm.validate((valid) => {
            if (valid) {
              this.saveLoading = true;
              const data = this.accountData;
              const addon_client_custom_field = {...this.ruleForm};
              this.clientCustomFieldList.forEach((item) => {
                if (item.type === "dropdown_text") {
                  addon_client_custom_field[item.id] =
                    item.select_select + "|" + this.ruleForm[item.id];
                }
              });
              const params = {
                ...data,
                customfield: {
                  addon_client_custom_field: addon_client_custom_field,
                },
              };
              updateAccount(params)
                .then((res) => {
                  if (res.data.status === 200) {
                    this.$message.success(res.data.msg);
                    if (this.origin_language !== params.language) {
                      localStorage.setItem("lang", params.language);
                      sessionStorage.setItem("brow_lang", params.language);
                      window.location.reload();
                      return;
                    }
                    this.getAccount();
                    this.getClientCustomFieldValue();
                    this.saveLoading = false;
                  }
                })
                .catch((error) => {
                  this.saveLoading = false;
                  this.$message.error(error.data.msg);
                });
            }
          });
        },
        handleCustomField(val) {
          if (val) {
            this.$message.warning(lang.account_tips_text1);
          }
        },
        // 展示修改密码弹框
        showPass() {
          if (this.prohibit_user_information_changes.includes("password")) {
            this.$message.warning(lang.account_tips_text1);
            return;
          }
          this.token = "";
          this.captcha = "";
          this.security_verify_method = "";
          this.security_verify_value = "";
          this.certify_id = "";
          this.isShowPass = true;
          let data = {
            old_password: "",
            new_password: "",
            repassword: "",
          };
          this.passData = data;
          this.errorText = "";
        },
        canChange() {
          return;
        },
        // 展示修改手机弹框
        showPhone() {
          if (
            this.orginAcountData.phone != "" &&
            this.prohibit_user_information_changes.includes("phone")
          ) {
            this.$message.warning(lang.account_tips_text1);
            return;
          }
          this.errorText = "";
          this.token = "";
          this.captcha = "";
          if (this.accountData.phone) {
            // 有手机号
            // 展示验证手机
            this.phoneData = {};
            this.isShowPhone = true;
          } else {
            // 展示绑定手机
            this.rePhoneData = {
              countryCode: 86,
            };
            this.isShowRePhone = true;
          }
        },
        // 展示修改邮箱弹框
        showEmail() {
          if (
            this.orginAcountData.email != "" &&
            this.prohibit_user_information_changes.includes("email")
          ) {
            this.$message.warning(lang.account_tips_text1);
            return;
          }
          this.errorText = "";
          this.emailData = {};
          this.reEmailData = {};
          this.token = "";
          this.captcha = "";
          if (this.accountData.email) {
            // 有邮箱
            // 展示验证邮箱
            this.isShowEmail = true;
          } else {
            // 展示绑定邮箱
            this.isShowReEmail = true;
          }
        },
        closePerate() {
          this.$refs.operaForm.resetFields();
          this.isShowPeratedia = false;
        },
        surePerate() {
          this.$refs.operaForm.validate((valid) => {
            if (valid) {
              this.subLoading = true;
              const params = {
                ...this.operateData,
                security_verify_method: this.security_verify_method,
                security_verify_value: this.security_verify_value,
                certify_id: this.certify_id,
              };
              updateOperationPassword(params)
                .then((res) => {
                  this.$message.success(res.data.msg);
                  this.closePerate();
                  this.subLoading = false;
                  this.getAccount();
                })
                .catch((err) => {
                  this.subLoading = false;
                  this.$message.error(err.data.msg);
                  this.security_verify_method = "";
                  this.security_verify_value = "";
                  this.certify_id = "";
                  if (
                    err?.data?.data?.need_security_verify === true &&
                    err?.data?.data?.available_methods?.length > 0
                  ) {
                    this.actionType = "update_operate_password";
                    this.$refs.securityRef.openDialog(
                      "surePerate",
                      err.data.data.available_methods
                    );
                  }
                });
            }
          });
        },

        hadelSecurityConfirm(callbackFun, securityForm) {
          this.security_verify_method = securityForm.security_verify_method;
          this.security_verify_value = securityForm.security_verify_value;
          this.certify_id = securityForm.certify_id;
          this[callbackFun]();
        },

        // 确认修改密码
        doPassEdit() {
          let isPass = true;
          const data = {
            ...this.passData,
            security_verify_method: this.security_verify_method,
            security_verify_value: this.security_verify_value,
            certify_id: this.certify_id,
          };
          if (!data.old_password) {
            this.errorText = lang.account_tips25;
            isPass = false;
            return;
          } else {
            if (data.old_password.length < 6 || data.old_password.length > 32) {
              this.errorText = lang.account_tips26;
              isPass = false;
              return;
            }
          }

          if (!data.new_password) {
            this.errorText = lang.account_tips27;
            isPass = false;
            return;
          } else {
            if (data.new_password.length < 6 || data.new_password.length > 32) {
              this.errorText = lang.account_tips28;
              isPass = false;
              return;
            }
          }

          if (!data.repassword) {
            this.errorText = lang.account_tips29;
            isPass = false;
            return;
          } else {
            if (data.repassword.length < 6 || data.repassword.length > 32) {
              this.errorText = lang.account_tips30;
              isPass = false;
              return;
            }
            if (data.repassword !== data.new_password) {
              this.errorText = lang.account_tips31;
              isPass = false;
              return;
            }
          }
          if (isPass) {
            this.errorText = "";
            updatePassword(data)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(lang.account_tips32);
                  this.isShowPass = false;
                  location.href = "login.htm";
                  // 执行登录接口
                }
              })
              .catch((err) => {
                this.errorText = err.data.msg;
                this.security_verify_method = "";
                this.security_verify_value = "";
                this.certify_id = "";
                if (
                  err?.data?.data?.need_security_verify === true &&
                  err?.data?.data?.available_methods?.length > 0
                ) {
                  this.actionType = "update_password";
                  this.$refs.securityRef.openDialog(
                    "doPassEdit",
                    err.data.data.available_methods
                  );
                }
              });
          }
        },
        // 验证原手机号
        doPhoneEdit() {
          let isPass = true;
          if (!this.phoneData.code) {
            isPass = false;
            this.errorText = lang.account_tips33;
          } else {
            if (this.phoneData.code.length !== 6) {
              isPass = false;
              this.errorText = lang.account_tips34;
            }
          }
          if (isPass) {
            this.errorText = "";
            const params = {
              code: this.phoneData.code,
            };
            verifiedPhone(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(lang.account_tips35);
                  this.isShowPhone = false;
                  this.isShowRePhone = true;
                }
              })
              .catch((error) => {
                this.errorText = error.data.msg;
                // this.$message.error(error.data.msg)
              });
          }
        },
        // 修改手机号
        doRePhoneEdit() {
          let isPass = true;
          if (!this.rePhoneData.phone) {
            isPass = false;
            this.errorText = lang.account_tips36;
          } else {
            if (this.rePhoneData.phone.length !== 11) {
              isPass = false;
              this.errorText = lang.account_tips37;
            }
          }
          if (!this.rePhoneData.code) {
            isPass = false;
            this.errorText = lang.account_tips33;
          } else {
            if (this.rePhoneData.code.length !== 6) {
              isPass = false;
              this.errorText = lang.account_tips34;
            }
          }
          if (isPass) {
            this.errorText = "";
            const params = {
              phone_code: this.rePhoneData.countryCode,
              phone: this.rePhoneData.phone,
              code: this.rePhoneData.code,
            };
            updatePhone(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(lang.account_tips38);
                  this.getAccount();
                  this.isShowRePhone = false;
                }
              })
              .catch((error) => {
                this.errorText = error.data.msg;
                // this.$message.error(error.data.msg)
              });
          }
        },
        // 验证原邮箱
        doEmailEdit() {
          let isPass = true;
          if (!this.emailData.code) {
            isPass = false;
            this.errorText = lang.account_tips33;
          } else {
            if (this.emailData.code.length !== 6) {
              isPass = false;
              this.errorText = lang.account_tips34;
            }
          }
          if (isPass) {
            this.errorText = "";
            const params = {
              code: this.emailData.code,
            };
            verifiedEmail(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(lang.account_tips39);
                  this.isShowEmail = false;
                  this.isShowReEmail = true;
                }
              })
              .catch((error) => {
                this.errorText = error.data.msg;
                // this.$message.error(error.data.msg)
              });
          }
        },
        // 修改邮箱
        doReEmailEdit() {
          let isPass = true;
          if (!this.reEmailData.code) {
            isPass = false;
            this.errorText = lang.account_tips33;
          } else {
            if (this.reEmailData.code.length !== 6) {
              isPass = false;
              this.errorText = lang.account_tips34;
            }
          }

          if (!this.reEmailData.email) {
            isPass = false;
            this.errorText = lang.account_tips19;
          }
          if (isPass) {
            this.errorText = "";
            const params = {
              code: this.reEmailData.code,
              email: this.reEmailData.email,
            };
            updateEmail(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(lang.account_tips39);
                  this.isShowReEmail = false;
                  this.getAccount();
                }
              })
              .catch((error) => {
                this.errorText = error.data.msg;
                // this.$message.error(error.data.msg)
              });
          }
        },
        doResetPass() {
          let isPass = true;
          const form = {...this.formData};
          // 邮件登录验证
          if (this.isEmailOrPhone) {
            if (!form.email) {
              isPass = false;
              this.errorText = lang.ali_tips1;
            } else if (
              form.email.search(
                /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/
              ) === -1
            ) {
              isPass = false;
              this.errorText = lang.account_tips40;
            }

            if (!form.emailCode) {
              isPass = false;
              this.errorText = lang.account_tips41;
            } else {
              if (form.emailCode.length !== 6) {
                isPass = false;
                this.errorText = lang.account_tips42;
              }
            }
          }

          // 手机号码登录 验证
          if (!this.isEmailOrPhone) {
            if (!form.phone) {
              isPass = false;
              this.errorText = lang.account_tips43;
            } else {
              // 设置正则表达式的手机号码格式 规则 ^起点 $终点 1第一位数是必为1  [3-9]第二位数可取3-9的数字  \d{9} 匹配9位数字
              const reg = /^\d+$/;
              if (!reg.test(form.phone)) {
                isPass = false;
                this.errorText = lang.account_tips44;
              }
            }

            if (!form.phoneCode) {
              isPass = false;
              this.errorText = lang.account_tips45;
            } else {
              if (form.phoneCode.length !== 6) {
                isPass = false;
                this.errorText = lang.account_tips46;
              }
            }
          }

          if (!form.password) {
            isPass = false;
            this.errorText = lang.account_tips47;
          } else if (form.password.length > 32 || form.password.length < 6) {
            isPass = false;
            this.errorText = lang.account_tips26;
          }
          if (!form.repassword) {
            isPass = false;
            this.errorText = lang.account_tips48;
          } else {
            if (form.password !== form.repassword) {
              isPass = false;
              this.errorText = lang.account_tips49;
            }
          }

          // 验证通过
          if (isPass) {
            this.errorText = "";
            let code = "";

            if (this.isEmailOrPhone) {
              code = form.emailCode;
            } else {
              code = form.phoneCode;
            }

            const params = {
              type: this.isEmailOrPhone ? "email" : "phone",
              account: this.isEmailOrPhone ? form.email : form.phone,
              phone_code: form.countryCode.toString(),
              code,
              password: form.password,
              re_password: form.repassword,
            };

            //调用注册接口
            forgetPass(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.$message.success(res.data.msg);
                  // // 存入 jwt
                  // localStorage.setItem("jwt", res.data.data.jwt);
                  location.href = "login.htm";
                }
              })
              .catch((err) => {
                this.errorText = err.data.msg;
                // this.$message.error(err.data.msg);
              });
          }
        },
        // 发送手机验证码
        sendPhoneCode(type) {
          let isPass = true;
          const tpyeConfig = {
            old: {
              action: "verify",
              phone_code: Number(this.accountData.phone_code),
              phone: this.accountData.phone,
              token: this.token,
              captcha: this.captcha,
              countDown: "phoneCodebtn",
              isShowCaptcha: this.commonData.captcha_client_verify == 1,
            },
            new: {
              action: "update",
              phone_code: Number(this.rePhoneData.countryCode),
              phone: this.rePhoneData.phone,
              token: this.token,
              captcha: this.captcha,
              countDown: "rePhoneCodebtn",
              isShowCaptcha: this.commonData.captcha_client_update == 1,
            },
            code: {
              action: "password_reset",
              phone_code: this.formData.countryCode,
              phone: this.formData.phone,
              token: this.token,
              captcha: this.captcha,
              countDown: "codePhoneCodebtn",
              isShowCaptcha: this.commonData.captcha_client_password_reset == 1,
            },
          };
          const params = tpyeConfig[type];
          if (type === "new") {
            if (!this.rePhoneData.phone) {
              this.errorText = lang.account_tips43;
              isPass = false;
            } else if (this.rePhoneData.phone.length !== 11) {
              this.errorText = lang.account_tips37;
              isPass = false;
            }
          }
          if (type === "code") {
            const form = this.formData;
            if (!form.phone) {
              isPass = false;
              this.errorText = lang.account_tips43;
            } else {
              // 设置正则表达式的手机号码格式 规则 ^起点 $终点 1第一位数是必为1  [3-9]第二位数可取3-9的数字  \d{9} 匹配9位数字
              const reg = /^\d+$/;
              if (!reg.test(form.phone)) {
                isPass = false;
                this.errorText = lang.account_tips44;
              }
            }
          }
          if (isPass) {
            this.errorText = "";
            if (tpyeConfig[type].isShowCaptcha && !this.captcha) {
              this.activeType = type;
              this.isShowCaptcha = true;
              this.codeAction = "phoneCode";
              this.$refs.captcha.doGetCaptcha();
              return;
            }
            phoneCode(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.errorText = "";
                  // 验证原手机 验证码按钮 执行倒计时
                  this.token = "";
                  this.captcha = "";
                  this.$refs[tpyeConfig[type].countDown].countDown();
                }
              })
              .catch((error) => {
                this.token = "";
                this.captcha = "";
                this.errorText = error.data.msg;
              });
          }
        },
        // 发送邮箱验证码
        sendEmailCode(type) {
          let isPass = true;
          const tpyeConfig = {
            old: {
              action: "verify",
              email: this.accountData.email,
              token: this.token,
              captcha: this.captcha,
              countDown: "emailCodebtn",
              isShowCaptcha: this.commonData.captcha_client_verify == 1,
            },
            new: {
              action: "update",
              email: this.reEmailData.email,
              token: this.token,
              captcha: this.captcha,
              countDown: "reEmailCodebtn",
              isShowCaptcha: this.commonData.captcha_client_update == 1,
            },
            code: {
              action: "password_reset",
              email: this.formData.email,
              token: this.token,
              captcha: this.captcha,
              countDown: "codeEmailCodebtn",
              isShowCaptcha: this.commonData.captcha_client_password_reset == 1,
            },
          };
          const params = tpyeConfig[type];
          if (type === "code") {
            const form = this.formData;
            if (!form.email) {
              isPass = false;
              this.errorText = lang.ali_tips1;
            } else if (
              form.email.search(
                /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/
              ) === -1
            ) {
              isPass = false;
              this.errorText = lang.account_tips40;
            }
          }
          if (isPass) {
            if (tpyeConfig[type].isShowCaptcha && !this.captcha) {
              this.activeType = type;
              this.isShowCaptcha = true;
              this.codeAction = "emailCode";
              this.$refs.captcha.doGetCaptcha();
              return;
            }
            this.errorText = "";
            emailCode(params)
              .then((res) => {
                if (res.data.status === 200) {
                  this.errorText = "";
                  // 验证原手机 验证码按钮 执行倒计时
                  this.token = "";
                  this.captcha = "";
                  this.$refs[tpyeConfig[type].countDown].countDown();
                }
              })
              .catch((error) => {
                this.token = "";
                this.captcha = "";
                this.errorText = error.data.msg;
              });
          }
        },
        // 获取通用配置
        getCommonData() {
          this.commonData = JSON.parse(
            localStorage.getItem("common_set_before")
          );
          this.prohibit_user_information_changes =
            this.commonData.prohibit_user_information_changes;
          document.title =
            this.commonData.website_name + "-" + lang.account_tips50;
        },
        // 监测滚动
        computeScroll() {
          return;
        },
        // 返回顶部
        goBackTop() {
          document.documentElement.scrollTop = document.body.scrollTop = 0;
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("configuration-login")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      mixins: [fixedFooter],
      data() {
        return {
          submitLoading: false,
          formData: {
            login_phone_verify: "",
            register_email: "",
            login_email_password: "",
            register_phone: "",
            code_client_email_register: "",
            home_login_check_ip: "",
            admin_login_check_ip: "",
            code_client_phone_register: "",
            limit_email_suffix: "",
            email_suffix: "",
            home_login_check_common_ip: "",
            home_login_ip_exception_verify: [],
            home_enforce_safe_method: [],
            admin_enforce_safe_method: [],
            admin_enforce_safe_method_scene: [],
            admin_allow_remember_account: "",
            first_login_method: "code",
            first_password_login_method: "email",
            admin_second_verify: 0,
            admin_second_verify_method_default: "",
            prohibit_admin_bind_phone: 0,
            prohibit_admin_bind_email: 0,
            admin_password_or_verify_code_retry_times: null,
            admin_frozen_time: null,
            admin_login_expire_time: null,
            home_login_expire_time: null,
            login_phone_password: 0,
            first_login_type: "",
            first_login_type_list: [],
            admin_login_ip_whitelist: "",
            /* 新增字段 */
            login_register_redirect_show: 1,
            login_register_redirect_text: "",
            login_register_redirect_url: "",
            login_register_redirect_blank: 1,
            admin_login_password_encrypt: 0,
            exception_login_certification_plugin: "",
          },
          homeVerifyList: [
            {
              label: lang.setting_text11,
              value: "operate_password",
            },
          ],
          homeSafeMethodList: [
            {
              label: lang.setting_text12,
              value: "phone",
            },
            {
              label: lang.setting_text13,
              value: "email",
            },
            {
              label: lang.setting_text14,
              value: "operate_password",
            },
            {
              label: lang.setting_text15,
              value: "certification",
            },
            {
              label: lang.setting_text16,
              value: "oauth",
            },
          ],
          homeSafeMethodType: [
            {
              label: lang.home_safe_method1,
              value: "operate_password",
            },
            {
              label: lang.home_safe_method2,
              value: "phone_code",
            },
            {
              label: lang.home_safe_method3,
              value: "email_code",
            },
            {
              label: lang.home_safe_method4,
              value: "certification",
            },
          ],
          adminMethodList: [
            {
              label: lang.setting_text14,
              value: "operate_password",
            },
          ],
          rules: {
            home_login_ip_exception_verify: [
              {
                required: false,
                message: lang.select + lang.setting_text9,
                type: "error",
              },
            ],
            admin_enforce_safe_method: [
              {
                required: false,
                message: lang.select + lang.setting_text19,
                type: "error",
              },
            ],
            home_enforce_safe_method: [
              {
                required: false,
                message: lang.select + lang.setting_text19,
                type: "error",
              },
            ],
            admin_login_ip_whitelist: [
              {
                validator: (val) => {
                  if (!val || val.trim() === "") {
                    return {result: true};
                  }
                  return this.validateIpWhitelist(val);
                },
                message:
                  lang.admin_login_ip_whitelist_error || "IP白名单格式错误",
                type: "error",
              },
            ],
          },
          isCanUpdata: sessionStorage.isCanUpdata === "true",
          hasController: true,
          adminScene: [
            {
              value: "all",
              label: lang.auth_all,
            },
            {
              value: "client_delete",
              label: lang.setting_text67,
            },
            {
              value: "update_client_status",
              label: lang.setting_text68,
            },
            {
              value: "host_operate",
              label: lang.setting_text69,
            },
            {
              value: "order_delete",
              label: lang.setting_text70,
            },
            {
              value: "clear_order_recycle",
              label: lang.setting_text71,
            },
            {
              value: "plugin_uninstall_disable",
              label: lang.setting_text72,
            },
          ],
          tabValue: "client",
          exception_login_certification_plugin_list: [],
        };
      },
      methods: {
        changeAdmin(val) {
          if (val.length === 0) {
            this.formData.admin_enforce_safe_method_scene = [];
          }
        },
        changeScene(val) {
          if (val.length === 0) {
            return;
          }
          const lastVal = val[val.length - 1];
          if (lastVal === "all") {
            this.formData.admin_enforce_safe_method_scene = ["all"];
          } else {
            this.formData.admin_enforce_safe_method_scene = val.filter(
              (item) => item !== "all"
            );
          }
        },
        // IP白名单格式验证
        validateIpWhitelist(val) {
          if (!val || val.trim() === "") {
            return {result: true};
          }

          const lines = val
            .split("\n")
            .map((line) => line.trim())
            .filter((line) => line !== "");

          for (let line of lines) {
            if (!this.isValidIpOrCidr(line)) {
              return {result: false};
            }
          }

          return {result: true};
        },
        // 验证单个IP或CIDR格式
        isValidIpOrCidr(ip) {
          if (!ip) return false;

          // CIDR格式检查
          if (ip.includes("/")) {
            const parts = ip.split("/");
            if (parts.length !== 2) return false;

            const [address, mask] = parts;
            const maskNum = parseInt(mask);

            // IPv4 CIDR
            if (this.isValidIPv4(address)) {
              return maskNum >= 0 && maskNum <= 32;
            }
            // IPv6 CIDR
            if (this.isValidIPv6(address)) {
              return maskNum >= 0 && maskNum <= 128;
            }

            return false;
          } else {
            // 单个IP检查
            return this.isValidIPv4(ip) || this.isValidIPv6(ip);
          }
        },
        // 验证IPv4地址
        isValidIPv4(ip) {
          const ipv4Regex =
            /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
          return ipv4Regex.test(ip);
        },
        // 验证IPv6地址
        isValidIPv6(ip) {
          const ipv6Regex =
            /^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/;
          return ipv6Regex.test(ip);
        },
        async getActivePlugin() {
          const res = await getActiveAddon();
          this.hasController = (res.data.data.list || [])
            .map((item) => item.name)
            .includes("TemplateController");
        },
        async onSubmit({validateResult, firstError}) {
          if (validateResult === true) {
            try {
              const params = JSON.parse(JSON.stringify(this.formData));
              if (
                params.admin_enforce_safe_method.length > 0 &&
                params.admin_enforce_safe_method_scene.length === 0
              ) {
                return this.$message.error(
                  `${lang.select}${lang.setting_text73}`
                );
              }
              this.submitLoading = true;
              const res = await updateLoginOpt(params);
              this.$message.success(res.data.msg);
              this.getSetting();
              this.submitLoading = false;
            } catch (error) {
              error.data?.msg && this.$message.error(error.data.msg);
              this.submitLoading = false;
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        async getSetting() {
          try {
            const res = await getLoginOpt();
            this.formData = res.data.data;
          } catch (error) {}
        },
      },
      created() {
        this.getActivePlugin();
        this.getSetting();
        this.exception_login_certification_plugin_list =
          JSON.parse(localStorage.getItem("common_set"))
            .exception_login_certification_plugin_list || [];
        document.title =
          lang.login_setting + "-" + localStorage.getItem("back_website_name");
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

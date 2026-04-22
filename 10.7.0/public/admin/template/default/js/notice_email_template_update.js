(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName(
      "notice-email-template-create"
    )[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
        comTinymce,
        comSendParams
      },
      data () {
        return {
          formData: {
            id: "",
            name: "",
            subject: "",
            notice_setting_name: "",
            message: "",
          },
          submitLoading: false,
          rules: {
            name: [
              {
                required: true,
                message: `${lang.input}${lang.nickname}`,
                type: "error",
              },
              {
                validator: (val) => val.length <= 100,
                message: `${lang.verify3}100`,
                type: "warning",
              },
            ],
            subject: [
              {
                required: true,
                message: `${lang.input}${lang.title}`,
                type: "error",
              },
              {
                validator: (val) => val.length <= 100,
                message: `${lang.verify3}100`,
                type: "warning",
              },
            ],
            message: [
              {
                required: true,
                message: `${lang.input}${lang.content}`,
                type: "error",
              },
            ],
          },
          actionList: [],
          treeProps: {
            valueMode: 'onlyLeaf',
            keys: {
              label: "name_lang",
              value: "name",
              children: "children",
            },
          },
        };
      },
      created () {
        this.formData.id = location.href.split("?")[1].split("=")[1];
        this.getEmailDetail();
        this.getActionList();
      },
      mounted () {
        // this.initTemplate()
        document.title =
          lang.email_notice +
          "-" +
          lang.template_manage +
          "-" +
          localStorage.getItem("back_website_name");
      },
      methods: {
        async getActionList () {
          try {
            const res = await getNoticeAction();
            const data = res.data.data;
            const group = {};
            data.list.forEach(item => {
              if (!group[item.type]) group[item.type] = [];
              group[item.type].push({ name: item.name, name_lang: item.name_lang });
            });
            this.actionList = data.type.map(cur => ({
              name: `p_${cur.name}`,
              name_lang: cur.name_lang,
              children: group[cur.name] || []
            }));
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        async getEmailDetail () {
          try {
            const res = await getEmailTemplateDetail(this.formData.id);
            Object.assign(this.formData, res.data.data.email_template);
            this.$nextTick(() => {
              this.$refs.comTinymce.setContent(this.formData.message);
              this.$refs.userDialog.clearValidate(['message']);
            });
          } catch (error) {
            console.log(error);
          }
        },
        setContent () {
          this.formData.message = this.$refs.comTinymce.getContent();
        },
        submit () {
          this.setContent();
          this.$refs.userDialog.validate().then(
            async (res) => {
              try {
                this.submitLoading = true;
                const res = await createEmailTemplate("update", this.formData);
                this.$message.success(res.data.msg);
                setTimeout(() => {
                  this.submitLoading = false;
                  location.href = "notice_email_template.htm";
                }, 500);
              } catch (error) {
                this.submitLoading = false;
                this.$message.error(error.data.msg);
              }
            },
            (error) => {
              console.log(error);
            }
          );
        },

        close () {
          location.href = "notice_email_template.htm";
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

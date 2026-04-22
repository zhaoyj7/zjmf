(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName('notice-send')[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      mixins: [fixedFooter],
      data () {
        return {
          data: [],
          tableLayout: false,
          bordered: true,
          visible: false,
          delVisible: false,
          statusVisble: false,
          hover: true,
          columns: [
            {
              colKey: 'name',
              title: lang.action_name,
              width: 250
            },
            {
              colKey: 'sms_global_name',
              title: lang.sms_global_name,
              minWidth: 130,
              ellipsis: true,
            },
            {
              colKey: 'sms_global_template',
              title: lang.sms_global_template,
              minWidth: 130,
              ellipsis: true,
            },
            {
              colKey: 'sms_name',
              title: lang.home_sms_interface,
              minWidth: 130,
              ellipsis: true,
            },
            {
              colKey: 'sms_template',
              title: lang.home_sms_template,
              minWidth: 130,
              ellipsis: true,
            },
            {
              colKey: 'sms_enable',
              title: lang.isOpen,
              width: 100
            },
            {
              colKey: 'email_name',
              title: lang.email_interface,
              minWidth: 130,
              ellipsis: true,
            },
            {
              colKey: 'email_template',
              title: lang.email_temp,
              minWidth: 130,
              ellipsis: true,
            },
            {
              colKey: 'email_enable',
              title: lang.isOpen,
              width: 100
            }
          ],
          hideSortTips: true,
          formData: {
            configuration: {
              send_sms: '',
              send_sms_global: '',
              send_email: ''
            }
          },
          loading: false,
          country: [],
          delId: '',
          curStatus: 1,
          statusTip: '',
          installTip: '',
          name: '', // 插件标识
          type: '', // 安装/卸载
          module: 'mail', // 当前模块
          smsList: [],  // 国内短信接口列表
          smsInterList: [],  // 国际短信接口列表
          emailList: [], // 邮件接口列表
          emailTemplateList: [], // 邮件模板列表
          interTempObj: {},
          tempObj: {},
          maxHeight: '',
          rules: {
            name: [
              { required: true, message: `${lang.select}${lang.notice_default_aciton}` },
            ],
            type: [
              { required: true, message: `${lang.select}${lang.interface_type}` },
            ],
          },
          canSend: true,
          submitLoading: false,
          /* ==== */
          tabs: [],
          activeTab: "",
          localSearch: "",
          resultList: [],
          searchLoading: false,
          popupProps: {
            overlayClassName: "com-low-level"
          },
          batchDialog: false,
          actionList: [],
          batchParams: {
            name: [],
            type: "",
            sms_name: "",
            sms_global_name: "",
            email_name: "",
          },
          checkAll: false,
          treeProps: {
            valueMode: 'onlyLeaf',
            keys: {
              label: "name_lang",
              value: "name",
              children: "children",
            },
          },
          showConfirm: false
        };
      },
      watch: {
        isCheckAll (val) {
          this.checkAll = val;
        },
        batchParams: {
          deep: true,
          handler (newVal) {
            this.batchParams.type = newVal.sms_name || newVal.sms_global_name || newVal.email_name ? 'valid' : '';
          }
        }
      },
      computed: {
        calcData () {
          return this.data.filter(item => item.type === this.activeTab);
        },
        isCheckAll () {
          const allServer = [];
          this.actionList.forEach((item) => {
            allServer.push(...item.children.map((server) => server.name));
          });
          return (
            this.batchParams.name.length === allServer.length
          );
        },
      },
      created () {
        // 发送管理列表
        this.getManageList();
        // 接口列表
        this.getSmsList();
        this.getEmailList();
        // 模板列表
        this.getEmailTemList();
        this.getActionList();
      },
      methods: {
        closeBatchDialog () {
          this.batchParams = {
            name: [],
            type: "",
            sms_name: "",
            sms_global_name: "",
            email_name: "",
          };
          this.$nextTick(() => {
            this.$refs.userDialog && this.$refs.userDialog.clearValidate();
            this.$refs.userDialog && this.$refs.userDialog.reset();
          });
          this.batchDialog = false;
        },
        chooseAll (val) {
          if (val) {
            const allServer = [];
            this.actionList.forEach((item) => {
              allServer.push(...item.children.map((server) => server.name));
            });
            this.batchParams.name = allServer;
          } else {
            this.batchParams.name = [];
          }
        },
        handleSubmit () {
          this.$refs.userDialog.validate().then(res => {
            if (res === true) {
              this.delVisible = true;
            }
          });
        },
        cancelDel () {
          this.delVisible = false;
        },
        async batchSubmit () {
          try {
            this.submitLoading = true;
            const res = await batchSendSetting({
              name: this.batchParams.name,
              sms_name: this.batchParams.sms_name,
              sms_global_name: this.batchParams.sms_global_name,
              email_name: this.batchParams.email_name,
            });
            this.delVisible = false;
            this.batchDialog = false;
            this.$message.success(res.data.msg);
            this.getManageList();
            this.visible = false;
            this.submitLoading = false;
            this.closeBatchDialog();
          } catch (error) {
            this.delVisible = false;
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
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
        batchConfig () {
          this.batchDialog = true;
          this.batchParams = {
            name: [],
            type: "",
            sms_name: "",
            sms_global_name: "",
            email_name: "",
          };
          this.showConfirm = false;
        },
        handleSearch (val) {
          if (val.trim()) {
            this.searchLoading = true;
            setTimeout(() => {
              this.resultList = this.data.reduce((all, cur) => {
                if (cur.name_lang.indexOf(val) !== -1) {
                  all.push({
                    label: cur.name_lang,
                    value: cur.name_lang,
                  });
                }
                return all;
              }, []);
              this.searchLoading = false;
            }, 10);
          }
        },
        chooseAction (val) {
          if (!val) {
            return this.resultList = [];
          }
          this.activeTab = this.data.find(item => item.name_lang === val)?.type || this.tabs[0]?.name;
        },
        // 切换短信接口清空短信模板
        changeInter (row) {
          // this.formData[row.name].sms_global_template = this.interTempObj[row.sms_global_name+'_interTemp'][0]?.id
          this.formData[row.name].sms_global_template = '';
        },
        changeHome (row) {
          // this.formData[row.name].sms_template = this.tempObj[row.sms_name+'_temp'][0]?.id || ''
          this.formData[row.name].sms_template = '';
        },
        // 根据短信name获取对应的模板
        async getSmsTemp (type, val) {
          try {
            const res = await getSmsTemplate(val);
            const temp = res.data.data.list.filter(item => {
              return item.type === 0;
            });
            const temp1 = res.data.data.list.filter(item => {
              return item.type === 1;
            });
            if (type === 1) {
              this.smsInterList.forEach(item => {
                if (item.name === val) {
                  this.$set(this.interTempObj, `${item.name}_interTemp`, temp1);
                  this.$forceUpdate();
                }
              });
            }
            if (type === 0) {
              this.smsList.forEach(item => {
                if (item.name === val) {
                  this.$set(this.tempObj, `${item.name}_temp`, temp);
                }
              });
            }
          } catch (error) {

          }
        },
        async getSmsList () {
          try {
            const res = await getSmsInterface();
            const temp = res.data.data.list;
            // 分装到国际/国内，在根据所选的接口name获取对应接口下面的模板
            temp.forEach(item => {
              if (item.sms_type.indexOf(1) !== -1) {
                this.smsInterList.push(item);
                this.getSmsTemp(1, item.name);
              }
              if (item.sms_type.indexOf(0) !== -1) {
                this.smsList.push(item);
                this.getSmsTemp(0, item.name);
              }
            });
          } catch (error) {

          }
        },
        async getEmailList () {
          try {
            const res = await getEmailInterface();
            this.emailList = res.data.data.list;
          } catch (error) {

          }
        },
        async getEmailTemList () {
          try {
            const res = await getEmailTemplate();
            this.emailTemplateList = res.data.data.list;
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        jump (row) {
          location.href = `notice_email_template.htm`;
        },
        async save () {
          try {
            const params = JSON.parse(JSON.stringify(this.formData));
            for (const item in params) {
              if (params[item].sms_template === '') {
                params[item].sms_template = 0;
              }
              if (params[item].email_template === '') {
                params[item].email_template = 0;
              }
              if (params[item].sms_global_template === '') {
                params[item].sms_global_template = 0;
              }
            }
            this.canSend = true;
            // 提交前验证，选择了接口的必填
            Object.keys(params).forEach(item => {
              try {
                if (params[item].sms_global_name && params[item].sms_global_template === 0 && params[item].sms_enable) { // 选择了国际接口未选择模板
                  this.canSend = false;
                  // 选择了短信接口未选择模板，滚动到对应的 requried 位置
                  throw new Error(lang.select + lang.sms_global_template);
                }
                if (params[item].sms_name && params[item].sms_template === 0 && params[item].sms_enable) { // 选择国内接口未选择模板
                  this.canSend = false;
                  throw new Error(lang.select + lang.home_sms_template);
                }
                if (params[item].email_name && params[item].email_template === 0 && params[item].email_enable) { // 选择了邮件未选择模板
                  this.canSend = false;
                  throw new Error(lang.select + lang.email_temp);
                }
              } catch (e) {
                this.$message.error(e.message);
              }
            });
            if (this.canSend) {
              this.submitLoading = true;
              const res = await updateSend(params);
              this.$message.success(res.data.msg);
              this.submitLoading = false;
            }
          } catch (error) {
            this.$message.error(error.data.msg);
            this.submitLoading = false;
          }
        },
        back () {
          window.history.go(-1);
        },
        // 获取列表
        async getManageList () {
          try {
            this.loading = true;
            const res = await getSendList();
            const temp = res.data.data.list;
            this.data = temp;
            this.tabs = res.data.data.type;
            this.activeTab = this.tabs[0].name;
            this.loading = false;
            // 动态渲染成响应式数据会很卡
            temp.forEach(item => {
              if (item.sms_template === 0) {
                item.sms_template = '';
              }
              if (item.email_template === 0) {
                item.email_template = '';
              }
              if (item.sms_global_template === 0) {
                item.sms_global_template = '';
              }
              //this.$set(this.formData, item.name, item)
              this.formData[item.name] = item;
            });
            this.formData.configuration = res.data.data.configuration;
          } catch (error) {
            this.loading = false;
            console.log(error);
          }
        }
      }
    }).$mount(template);
    typeof old_onload == 'function' && old_onload();
  };
})(window);

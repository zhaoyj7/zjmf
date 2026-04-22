(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("product-api")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data () {
        return {
          id: "",
          formData: {
            auto_setup: 1,
            type: "", // server server_group
            rel_id: "",
            show: 1,
            show_base_info: 0,
          },
          moduleType: "",
          submitLoading: false,
          checkOptions: [
            {
              value: 1,
              label: lang.open,
            },
            {
              value: 0,
              label: lang.close,
            },
          ],
          serverParams: {
            page: 1,
            limit: 20,
          },
          serverGroupParams: {
            page: 1,
            limit: 20,
          },
          rules: {
            type: [{ required: true, message: `${lang.choose_interface_type}` }],
            rel_id: [{ required: true, message: `${lang.choose_interface}` }],
          },
          total: 0,
          groupTotal: 0,
          serverList: [],
          serverGroupList: [],
          curList: [],
          content: "",
          isAgent: false,
          loadingModule: false,
        };
      },
      watch: {
        "formData.type": {
          immediate: true,
          handler (val) {
            if (!val) {
              return;
            }
            this.curList =
              val === "server" ? this.serverList : this.serverGroupList;
          },
        },
        // 'formData.rel_id': {
        //   immediate: true,
        //   handler (val) {
        //     if (val) {
        //       this.chooseId()
        //     }
        //   }
        // }
      },
      methods: {
        chooseInterfaceId (e) {
          this.formData.rel_id = e;
        },
        // 选择接口id
        async chooseId () {
          try {
            if (
              !this.$checkPermission(
                "auth_product_detail_server_product_configuration"
              )
            ) {
              return;
            }
            if (!this.formData.rel_id) {
              return;
            }
            const params = { ...this.formData };
            delete params.auto_setup;
            this.loadingModule = true;
            const res = await getProductConfig(this.id, params);
            this.$nextTick(() => {
              $(".config-box .content").html(res.data.data.content);
            });
            this.content = res.data.data.content;
            this.loadingModule = false;
          } catch (error) {
            this.loadingModule = false;
            this.$message.error(error.data.msg);
          }
        },
        changeType (type) {
          this.formData.type = type;
          this.formData.rel_id = "";
          this.curList = [];
        },
        async onSubmit ({ validateResult, firstError }) {
          if (validateResult === true) {
            try {
              this.submitLoading = true;
              const res = await editProductServer(this.id, this.formData);
              this.$message.success(res.data.msg);
              this.getUserDetail();
              this.submitLoading = false;
              // location.reload()
            } catch (error) {
              this.submitLoading = false;
              this.$message.error(error.data.msg);
            }
          } else {
            console.log("Errors: ", validateResult);
          }
        },
        async getUserDetail () {
          try {
            const res = await getProductDetail(this.id);
            const temp = res.data.data.product;
            document.title =
              lang.product_list +
              "-" +
              temp.name +
              "-" +
              localStorage.getItem("back_website_name");
            this.formData.auto_setup = temp.auto_setup;
            this.formData.type = temp.type;
            this.formData.rel_id = temp.rel_id || "";
            this.formData.show = temp.show;
            this.formData.show_base_info = temp.show_base_info;
            this.moduleType = temp.module;
            this.isAgent = temp.mode === "sync";
            $(".config-box .content").html("");
            let inter = await getInterface(this.serverParams);
            this.serverList = inter.data.data.list;
            this.total = inter.data.data.count;
            if (this.total > 20) {
              this.serverParams.limit = this.total;
              inter = await getInterface(this.serverParams);
              this.serverList = inter.data.data.list;
            }
            let group = await getGroup(this.serverGroupParams);
            this.groupTotal = group.data.data.count;
            this.serverGroupList = group.data.data.list;
            if (this.groupTotal > 20) {
              this.serverGroupParams.limit = this.groupTotal;
              group = await getGroup(this.serverGroupParams);
              this.serverGroupList = group.data.data.list;
            }
            this.curList =
              temp.type === "server" ? this.serverList : this.serverGroupList;
            // this.formData.rel_id = this.curList[0].id
            this.chooseId();
            this.$forceUpdate();
          } catch (error) {
            console.log(error);
          }
        },
        back () {
          location.href = "product.htm";
        },
      },
      created () {
        this.id = location.href.split("?")[1].split("=")[1];
        this.getUserDetail();
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

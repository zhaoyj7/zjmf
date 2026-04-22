(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("log-system")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    new Vue({
      components: {
        comConfig,
      },
      created() {
        const params = getQuery();
        this.id = params.id;
        this.getMssageDetail();
      },
      data() {
        return {
          id: "",
          loading: false,
          submitLoading: false,
          delId: [],
          delVisible: false,
          messageDetail: {},
          before: {},
          next: {},
        };
      },
      methods: {
        goBack() {
          location.href = `message_list.htm?type=${this.messageDetail.type}`;
        },
        goNextMessage(id) {
          location.href = "message_detail.htm?id=" + id;
        },
        async getMssageDetail() {
          try {
            this.loading = true;
            const res = await apiNoticeDetail({id: this.id});
            this.messageDetail = res.data.data.notice;
            this.before = res.data.data.before;
            this.next = res.data.data.next;
            this.loading = false;
          } catch (error) {
            this.$message.error(error.data.msg);
            this.loading = false;
          }
        },
        handelDelete(ids = []) {
          if (ids.length === 0) {
            this.$message.warning(lang.notice_text15);
            return;
          }
          this.delId = ids;
          this.delVisible = true;
        },
        async sureDel() {
          try {
            this.submitLoading = true;
            const res = await apiNoticeDel({ids: this.delId});
            this.$message.success(res.data.msg);
            this.delVisible = false;
            this.submitLoading = false;
            this.goBack();
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        cancelDel() {
          this.delId = [];
          this.delVisible = false;
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

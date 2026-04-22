(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("log-system")[0];
    Vue.prototype.lang = window.lang;
    Vue.prototype.moment = window.moment;
    new Vue({
      components: {
        comConfig,
        comPagination,
      },
      created() {
        const params = getQuery();
        if (params.type) {
          this.params.type = params.type;
        }
        this.getMssageList();
      },
      data() {
        return {
          data: [],
          bordered: true,
          visible: false,
          delVisible: false,
          hover: true,
          tableLayout: false,
          columns: [
            {
              colKey: "row-select",
              type: "multiple",
              width: 50,
              fixed: "left",
            },
            {
              colKey: "title",
              title: lang.notice_text5,
              minWidth: 300,
              ellipsis: true,
            },
            {
              colKey: "accept_time",
              title: lang.notice_text6,
              width: 200,
            },
            {
              colKey: "op",
              width: 120,
              title: lang.operation,
              cell: "op",
              fixed: "right",
            },
          ],
          params: {
            keywords: "",
            page: 1,
            limit: getGlobalLimit(),
            orderby: "id",
            sort: "desc",
            read: "",
            type: "idcsmart",
          },
          id: "",
          total: 0,
          pageSizeOptions: [10, 20, 50, 100],
          loading: false,
          title: "",
          delId: [],
          selectedRowKeys: [],
          submitLoading: false,
        };
      },
      methods: {
        changeType(type) {
          this.params.type = type;
          this.params.page = 1;
          this.selectedRowKeys = [];
          this.getMssageList();
        },
        changePage(e) {
          this.params.page = e.current;
          this.params.limit = e.pageSize;
          this.getMssageList();
        },
        handelRead(ids, isAll = false) {
          if (!isAll && ids.length === 0) {
            this.$message.warning(lang.notice_text15);
            return;
          }
          const params = isAll ? {all: 1} : {ids: ids};
          apiNoticeRead(params)
            .then((res) => {
              this.$message.success(res.data.msg);
              this.getMssageList();
            })
            .catch((err) => {
              this.$message.error(err.data.msg);
            });
        },
        rehandleSelectChange(value, {selectedRowData}) {
          this.selectedRowKeys = value;
        },
        async getMssageList() {
          try {
            this.loading = true;
            const res = await apiNoticeList(this.params);
            this.data = res.data.data.list;
            this.total = res.data.data.count;
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
            this.getMssageList();
            this.submitLoading = false;
          } catch (error) {
            this.submitLoading = false;
            this.$message.error(error.data.msg);
          }
        },
        cancelDel() {
          this.delId = [];
          this.delVisible = false;
        },
        clearKey() {
          this.params.keywords = "";
          this.search();
        },
        search() {
          this.params.page = 1;
          this.getMssageList();
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

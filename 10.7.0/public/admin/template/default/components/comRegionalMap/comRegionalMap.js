/* 区域映射-用于 mf_cloud mf_cloud_ip mf_cloud_mysql */
const comRegionalMap = {
  template: `
    <div class="box com-regional-map">
      <div class="com-top flex">
        <t-button class="add-btn" @click="addRegional" :disabled="isAgent">{{lang.order_text53}}</t-button>
        <div class="flex">
          <t-input v-model="params.name" class="ml_10" clearable placeholder="请输入区域组名" @clear="clearKey" 
            @keypress.enter.native="getRegionalData">
          </t-input>
          <t-input v-model="params.keywords" class="ml_10" clearable placeholder="请输入区域、商品、描述" @clear="clearKey" 
            @keypress.enter.native="getRegionalData">
          </t-input>
          <t-button class="ml_10" @click="getRegionalData">{{lang.search}}</t-button>
        </div>
      </div>
      <t-table
        row-key="id"
        size="medium"
        display-type="fixed-width"
        class="config-table"
        :data="regionalData"
        :columns="regionalColumns"
        :hover="hover"
        :loading="loading"
        :table-layout="tableLayout ? 'auto' : 'fixed'"
        :hide-sort-tips="true">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #product="{row}">
          <span v-for="(item, index) in row.product" :key="item.id">
            {{item.name}}
            <template v-if="item.data_center.length > 0">
              (
                <span v-for="(sub, subIndex) in item.data_center" :key="sub.id">
                  <span v-if="subIndex > 0">, </span>
                  {{sub.name}}
                </span>
              )
            </template>
            <span v-if="index < row.product.length - 1">, </span>
          </span>
        </template>
        <template #description="{row}">{{row.description || "--"}}</template>
        <template #op="{row}">
          <div class="com-opt">
            <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
              <t-icon name="edit" class="common-look" @click="editRegional(row)" :class="{'server-disabled': isAgent}"></t-icon>
            </t-tooltip>
            <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
              <t-icon name="delete" class="common-look" @click="deleteRegional(row)" :class="{'server-disabled': isAgent}"></t-icon>
            </t-tooltip>
          </div>
        </template>
      </t-table>
      <com-pagination v-if="total" :total="total" :page="params.page" :limit="params.limit" @page-change="changePage">
      </com-pagination>
      <t-dialog :visible.sync="visible" :header="addTip" :on-close="close" :footer="false" width="600"
        class="admin-dialog">
        <t-form :rules="rules" :data="formData" ref="formDialog" @submit="onSubmit" 
          label-align="top" reset-type="initial">
          <t-form-item :label="lang.regional_group_name" name="name">
            <t-input :placeholder="lang.regional_group_name" v-model="formData.name" />
          </t-form-item>
          <t-form-item :label="lang.product" name="role_id" class="required product-map">
            <div class="product-item flex" v-for="(item, index) in formData.data_center" :key="item.id">
              <t-form-item :label="lang.group" :name="'data_center[' + index + '].product_id'" class="no-lable">
                <t-tree-select
                  :data="productList"
                  v-model="item.product_id"
                  :treeProps="treeProps"
                  filterable
                  clearable @change="changeProduct(item, index)">
                </t-tree-select>
              </t-form-item>
              <t-form-item :label="lang.group" :name="'data_center[' + index + '].data_center_id'" class="no-lable">
                <t-select v-model="item.data_center_id" :placeholder="lang.group"
                  :disabled="!item.product_id" multiple filterable clearable :min-collapsed-num="1">
                  <t-option v-for="item in item.areaList" :value="item.id" :label="item.name" :key="item.id">
                  </t-option>
                </t-select>
              </t-form-item>
              <t-icon name="minus-circle" class="sub common-look" @click="subItem(index)" v-show="formData.data_center.length > 1"></t-icon>
              <t-icon name="add-circle" class="add common-look" @click="addItem" v-show="index === formData.data_center.length - 1">
              </t-icon>
            </div>
          </t-form-item>
          <t-form-item :label="lang.description" name="description">
            <t-textarea :placeholder="lang.description" v-model="formData.description" />
          </t-form-item>
          <div class="com-f-btn">
            <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
            <t-button theme="default" variant="base" @click="close">{{lang.cancel}}</t-button>
          </div>
        </t-form>
      </t-dialog>
      <!-- 删除弹窗 -->
      <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
        <template slot="footer">
          <t-button theme="primary" @click="sureDel" :loading="submitLoading">{{lang.sure}}</t-button>
          <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
        </template>
      </t-dialog>
    </div>
    `,
  components: {
    comPagination
  },
  props: {
    module: { // 模块名称 mf_cloud mf_cloud_ip mf_cloud_mysql
      type: String,
      required: true,
    },
    isAgent: {
      type: Boolean,
      default: false,
    }
  },
  computed: {
    disabledProList () {
      const selectedProductIds = this.formData.data_center.map(item => item.product_id);
      return selectedProductIds;
    }
  },
  data () {
    return {
      tableLayout: false,
      hover: true,
      hideSortTips: true,
      loading: false,
      params: {
        name: '',
        keywords: '',
        page: 1,
        type: '',
        limit: getGlobalLimit(),
      },
      total: 0,
      regionalData: [],
      regionalColumns: [
        {
          colKey: "name",
          title: lang.regional_group,
          width: 200,
          ellipsis: true,
        },
        {
          colKey: "product",
          title: lang.regional_product_area,
          ellipsis: true,
          minWidth: 300,
        },
        {
          colKey: "description",
          title: lang.description,
          width: 200,
          ellipsis: true,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 120
        },
      ],
      productList: [],
      delVisible: false,
      curId: "",
      submitLoading: false,
      visible: false,
      addTip: "",
      optType: "add",
      treeProps: {
        valueMode: "onlyLeaf",
        keys: {
          label: "name",
          value: "id",
        },
        expandOnClickNode: true,
      },
      rules: {
        name: [
          { required: true, message: `${lang.input}${lang.regional_group_name}`, trigger: "blur" },
        ],
        product_id: [
          { required: true, message: `${lang.select}${lang.product}` },
        ],
        data_center_id: [
          { required: true, message: `${lang.select}${lang.temp_area}` },
        ]
      },
      formData: {
        name: "",
        description: "",
        data_center: [{
          product_id: "",
          data_center_id: [],
          areaList: []
        }]
      }
    };
  },
  created () {
    this.initData();
    this.getProduct();
  },
  methods: {
    initDisabledPro () {
      this.productList.forEach((item) => {
        item.children.forEach((ele) => {
          if (
            ele.children.every(child =>
              this.disabledProList.includes(child.id)
            )
          ) {
            ele.disabled = true;
          }
          ele.children.forEach(child => {
            child.disabled = this.disabledProList.includes(child.id);
          });
        });
        if (item.children.every(ele => ele.disabled)) {
          item.disabled = true;
        }
      });
    },
    addRegional () {
      this.visible = true;
      this.addTip = `${lang.add}${lang.regional_group}`;
      this.optType = "add";
      this.formData = {
        name: "",
        description: "",
        data_center: [{
          product_id: "",
          data_center_id: [],
          areaList: []
        }]
      };
      this.$nextTick(() => {
        if (this.$refs.formDialog) {
          setTimeout(() => {
            this.$refs.formDialog.reset();
            this.$refs.formDialog.clearValidate();
          }, 0);
        }
      });
    },
    close () {
      this.visible = false;
    },
    subItem (index) {
      this.formData.data_center.splice(index, 1);
    },
    addItem () {
      this.formData.data_center.push({
        product_id: null,
        data_center_id: []
      });
      this.initDisabledPro();
    },
    async changeProduct (item, index) {
      try {
        const res = await this.getProductDataCenter(item.product_id);
        item.areaList = res;
        this.$forceUpdate();
      } catch (error) { }
    },
    async getProductDataCenter (id) {
      try {
        const res = await getProductDataCenter({
          id: id
        });
        const temp = res.data.data.list;
        return temp;
      } catch (error) {
        this.$message.error(error.data.msg);
        return [];
      }
    },

    async onSubmit ({ validateResult, firstError }) {
      if (validateResult === true) {
        try {
          const temp = JSON.parse(JSON.stringify(this.formData));
          if (this.optType === "add") {
            delete temp.id;
          }
          this.submitLoading = true;
          const res = await addAndEditRegional(this.optType, temp);
          this.$message.success(res.data.msg);
          this.params.page = 1;
          this.getRegionalData();
          this.visible = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
      }
    },
    editRegional (row) {
      if (this.isAgent) {
        return;
      }
      this.visible = true;
      this.addTip = `${lang.edit}${lang.regional_group}`;
      this.optType = "update";
      this.formData = {
        id: row.id,
        name: row.name,
        description: row.description,
        data_center: []
      };
      const dataCenters = row.product.map((item) => {
        let temp = {
          product_id: item.id,
          data_center_id: item.data_center.map(ele => ele.id)
        };
        return this.getProductDataCenter(item.id)
          .then(res => {
            temp.areaList = res;
            return temp;
          })
          .catch(error => {
            temp.areaList = [];
            return temp;
          });
      });
      Promise.all(dataCenters).then(result => {
        this.formData.data_center = result;
      });
    },
    deleteRegional (row) {
      if (this.isAgent) {
        return;
      }
      this.curId = row.id;
      this.delVisible = true;
    },
    async sureDel () {
      try {
        this.submitLoading = true;
        const res = await deleteRegional({
          id: this.curId
        });
        this.$message.success(res.data.msg);
        this.params.page = this.regionalData.length > 1 ? this.params.page : this.params.page - 1;
        this.delVisible = false;
        this.getRegionalData();
        this.submitLoading = false;
      } catch (error) {
        this.submitLoading = false;
        this.delVisible = false;
        this.$message.error(error.data.msg);
      }
    },
    initData () {
      this.params = {
        name: '',
        keywords: '',
        page: 1,
        limit: getGlobalLimit(),
      };
      this.getRegionalData();
    },
    clearKey () {
      this.params.page = 1;
      this.getRegionalData();
    },
    changePage (e) {
      this.params.page = e.current;
      this.params.limit = e.pageSize;
      this.getRegionalData();
    },
    async getRegionalData () {
      try {
        this.loading = true;
        const res = await getRegionalList({
          ...this.params,
          type: this.isAgent ? 'upstream' : 'local',
        });
        this.regionalData = res.data.data.list;
        this.total = res.data.data.count;
      } catch (error) {
        this.$message.error(error.data.msg);
      } finally {
        this.loading = false;
      }
    },
    async getProduct () {
      try {
        const res = await getModuleProduct({
          module: ['mf_cloud', 'mf_cloud_ip', 'mf_cloud_mysql'],
          type: 0
        });
        this.productList = res.data.data.list.map((item) => {
          item.id = `f_${item.id}`;
          item.children = item.child.map((el) => {
            el.id = `s_${el.id}`; 
            el.children = el.child;
            delete el.child;
            return el;
          });
          delete item.child;
          return item;
        });
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
  }
};

/* 视图字段 */
const comViewFiled = {
  template: `
    <div class="view-filed">
      <!-- <t-select :value="id" :label="lang.view_filed + ':'" class="choose-view" @change="chooseView" :class="{'not-default': id !== defaultId}"
      :popupProps="popupProps" v-if="view === 'host'">
        <t-option v-for="item in admin_view_list" :value="item.id" :label="item.name" :key="item.id">
        </t-option>
        <div class="bot-opt" slot="panelBottomContent">
          <t-option key="new" value="new" :label="lang.field_add_view"></t-option>
          <t-option key="manage" value="manage" :label="lang.field_manage_view"></t-option>
        </div>
      </t-select>
      <t-tooltip :show-arrow="false" theme="light" placement="top-left" overlayClassName="view-change-tip" v-if="view === 'host'">
        <template slot="content">
          <p>{{lang.field_tip1}}</p>
          <p>{{lang.field_tip2}}</p>
          <p>{{lang.field_tip3}}</p>
          <p>{{lang.field_tip4}}</p>
        </template>
        <t-icon name="help-circle" class="view-tip"></t-icon>
      </t-tooltip> -->
      <t-tooltip :content="lang.field_setting" :show-arrow="false" theme="light" placement="top-left">
        <t-icon name="setting" @click="handleFiled" class="set-icon"></t-icon>
      </t-tooltip>
      <!-- 字段设置 -->
      <t-dialog :header="calcTit" :visible.sync="filedModel" :footer="false" width="1109"
        class="filed-dialog" placement="center">
        <div class="con">
          <div class="filed-box">
            <t-input :placeholder="lang.field_search" v-model="keywords" clearable class="top">
              <template #suffixIcon>
                <t-icon name="search"></t-icon>
              </template>
            </t-input>
            <div class="scroll t-table__content">
              <div class="type-item" v-for="(item,index) in filterField" :key="index">
                <p class="s-tit" v-if="item.field.length > 0">
                  {{item.name}}
                  <t-tooltip :content="lang.product_field_tip" :show-arrow="false" theme="light"
                    placement="top-left" v-if="view === 'host' && index === 2">
                    <t-icon name="help-circle"></t-icon>
                  </t-tooltip>
                </p>
                <div class="filed">
                  <p class="item" v-for="el in item.field" :key="el.key">
                    <t-checkbox v-model="el.checked" :title="el.name" :disabled="el.key === 'id'"
                    @change="changeField($event, el.key)">
                      <span v-html="replaceText(el)" v-if="el.is_global !== 1"></span>
                      <span v-else>
                        <span class="com-red">(${lang.product_set_text133})</span>
                        <span v-html="replaceText(el)"></span>
                      </span>
                    </t-checkbox>
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="select-filed">
            <p class="top first-text-color">{{lang.cur_choose_field}}</p>
            <div class="scroll t-table__content">
              <t-table row-key="key" :data="calcSelectFiled" size="medium" :columns="filedColumns" :hover="hover"
                table-layout="fixed" display-type="fixed-width" :hide-sort-tips="true"
                drag-sort="row-handler" @drag-sort="changeSort">
                <template slot="sortIcon">
                  <t-icon name="caret-down-small"></t-icon>
                </template>
                <template #drag="{row}">
                  <t-icon name="move"></t-icon>
                </template>
                <template #op="{row}">
                  <div class="com-opt">
                    <t-icon class="common-look" style="color: #A2A2A2;" name="delete" @click="delField('filed', row)" v-if="row.key !== 'id'"></t-icon>
                  </div>
                </template>
              </t-table>
            </div>
          </div>
        </div>
        <div class="view-bot">
          <div class="left">
            <t-button theme="primary" @click="saveAsView" v-if="view==='host' || view === 'order'">
            {{lang.save_as_view}}
            <t-tooltip :content="lang.save_as_tip" :show-arrow="false" theme="light" placement="top-left">
              <t-icon name="help-circle"></t-icon>
            </t-tooltip>
            </t-button>
          </div>
          <div class="com-f-btn">
            <t-button theme="primary" @click="submitField" :loading="submitLoading">{{lang.sure}}</t-button>
            <t-button theme="default" variant="base" @click="filedModel=false">{{lang.cancel}}</t-button>
          </div>
        </div>
      </t-dialog>
      <!-- 视图管理 -->
      <t-dialog :header="lang.field_manage_view" :visible="viewManageDialog" :footer="false" width="750"
      class="field_manage_dialog" @close="viewManageDialog=false">
        <div class="con">
          <t-form :data="viewManageForm" ref="userDialog" @submit="onSubmit">
            <t-form-item :label="lang.field_default_show" class="short">
              <t-form-item>
                <t-select v-model="viewManageForm.type" @change="changeViewType">
                  <t-option :key="0" :label="lang.specify_view" value="specify"></t-option>
                  <t-option :key="1" :label="lang.last_scan_view" value="scan"></t-option>
                </t-select>
              </t-form-item>
              <t-form-item v-if="viewManageForm.type === 'specify'">
                <t-select v-model="viewManageForm.choose">
                  <t-option :key="item.id" :label="item.name" :value="item.id" v-for="item in viewManageChoose"></t-option>
                </t-select>
              </t-form-item>
            </t-form-item>
            <t-form-item :label="lang.view_list">
              <t-table row-key="id" :data="viewManageList" size="medium" :columns="viewColumns" :hover="hover" :loading="loading"
                table-layout="auto" display-type="fixed-width" :hide-sort-tips="true" max-height="450px" drag-sort="row-handler"
                @drag-sort="changeViewManageSort">
                <template slot="sortIcon">
                  <t-icon name="caret-down-small"></t-icon>
                </template>
                <template #drag="{row}">
                  <t-icon name="move"></t-icon>
                </template>
                <template #status="{row}">
                  <t-switch size="medium" :custom-value="[1,0]" v-model="row.status"
                  v-if="row.default === 0" @change="changeViewStatus($event, row)"></t-switch>
                  <span v-else>--</span>
                </template>
                <template #op="{row}">
                  <t-tooltip :content="lang.copy" :show-arrow="false" theme="light">
                    <t-icon name="file-copy" size="18px" @click="copyHandler(row)" class="common-look"></t-icon>
                  </t-tooltip>
                  <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
                    <t-icon name="edit-1" size="18px" @click="editHandler(row)" class="common-look" v-if="row.default !== 1"></t-icon>
                  </t-tooltip>
                  <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
                    <t-icon name="delete" size="18px" @click="deleteHandler(row)" class="common-look" v-if="row.default !== 1"></t-icon>
                  </t-tooltip>
                </template>
              </t-table>
            </t-form-item>
            <div class="com-f-btn">
              <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.sure}}</t-button>
              <t-button theme="default" variant="base" @click="viewManageDialog=false">{{lang.cancel}}</t-button>
            </div>
          </t-form>
        </div>
      </t-dialog>
      <!-- 删除视图 -->
      <t-dialog theme="warning" :header="lang.sureDelete" :close-btn="false" :visible.sync="delVisible">
        <template slot="footer">
          <t-button theme="primary" @click="sureDel" :loading="submitLoading">{{lang.sure}}</t-button>
          <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
        </template>
      </t-dialog>
      <!-- 新建/编辑视图 -->
      <t-dialog :header="viewTit" :visible.sync="viewModel" :footer="false" width="1100"
        class="filed-dialog view-dialog" @close="closeView" placement="center">
        <div class="con">
          <t-form :data="viewForm" ref="viewForm" @submit="submitView" :rules="rules">
            <template v-if="!isOutEdit">
              <t-form-item :label="lang.view_name" name="name">
                <t-input v-model="viewForm.name" class="top" :placeholder="lang.input+lang.view_name" :maxlength="20" show-limit-number></t-input>
              </t-form-item>
              <t-form-item :label="lang.display_fields">
                <div class="filed">
                  <div class="filed-box">
                    <t-input :placeholder="lang.field_search" v-model="viewKeywords"
                    clearable class="top" @change="changeSearch">
                      <template #suffixIcon>
                        <t-icon name="search"></t-icon>
                      </template>
                    </t-input>
                    <div class="scroll t-table__content">
                      <t-tree :data="viewFiledArr" checkable activable :line="true" :filter="viewFilter"
                      :active-multiple="false" v-model="viewForm.select_field"
                      :keys="{value: 'key', label:'name', children:'field'}" ref="tree"
                      @change="changeCheck" :expand-on-click-node="false" :indetesrminate="true">
                      </t-tree>
                    </div>
                  </div>
                  <div class="select-filed">
                    <p class="top">{{lang.cur_choose_field}}</p>
                    <div class="scroll t-table__content">
                      <t-table row-key="key" :data="calcSelectFiled" size="medium" :columns="filedColumns" :hover="hover"
                        table-layout="fixed" display-type="fixed-width" :hide-sort-tips="true" drag-sort="row-handler"
                        @drag-sort="changeViewSort">
                        <template slot="sortIcon">
                          <t-icon name="caret-down-small"></t-icon>
                        </template>
                        <template #drag="{row}">
                          <t-icon name="move"></t-icon>
                        </template>
                        <template #op="{row}">
                          <div class="com-opt">
                            <t-icon name="delete" @click="delField('view',row)" v-if="row.key !== 'id'"></t-icon>
                          </div>
                        </template>
                      </t-table>
                    </div>
                  </div>
                </div>
              </t-form-item>
              <t-form-item :label="lang.data_range">
                <t-switch size="medium" :custom-value="[1,0]" @change="changeRangeSwitch"
                v-model="viewForm.data_range_switch">
                </t-switch>
              </t-form-item>
            </template>
            <t-form-item :label="lang.view_name" name="name" v-else>
              <t-input v-model="viewForm.name" class="top" :placeholder="lang.input+lang.view_name" disabled></t-input>
            </t-form-item>
            <t-form-item :label="isOutEdit ? lang.data_range: ' ' " v-show="viewForm.data_range_switch" class="range-item">
              <t-button v-if="viewForm.data_range_switch && viewForm.select_data_range.length === 0" @click="addRange">{{lang.order_text53}}</t-button>
              <t-table row-key="key" :data="viewForm.select_data_range" size="medium" :columns="rangeColumns" :hover="hover" :loading="loading"
                table-layout="fixed" display-type="fixed-width" class="range-table"
                :hide-sort-tips="true" :max-height="222">
                <template #index="{row,rowIndex}">
                  {{rowIndex + 1}}
                </template>
                <template #key="{row}">
                  <t-tree-select
                    :data="calcDisabelViewRangeData"
                    v-model="row.key"
                    :treeProps="treeProps"
                    :popupProps="{overlayClassName: 'view-dailog-tree'}"
                    filterable
                    clearable @change="changeKey">
                  </t-tree-select>
                </template>
                <template #rule="{row}">
                  <t-select v-model="row.rule" @change="changeRule($event, row)">
                    <t-option :value="item.value" :label="item.label" v-for="item in calcCurFiled(row.key).rule" :key="item.value">
                    </t-option>
                  </t-select>
                </template>
                <template #value="{row, rowIndex}">
                  <template v-if="row.rule !== 'empty' && row.rule !== 'not_empty'">
                    <!-- input | multi_select | select | date -->
                    <template v-if="calcCurFiled(row.key).type === 'input'">
                      <t-input-number :placeholder="lang.input" v-model="row.value" theme="normal" @blur="handleNumber($event, row.key)"
                        v-if="row.key === 'id' || row.key === 'client_id'" :decimal-places="0">
                      </t-input-number>
                      <t-input-number :placeholder="lang.input" v-model="row.value" theme="normal" @blur="handleNumber($event, row.key)"
                        v-else-if="isPriceType(row.key)" :decimal-places="2">
                      </t-input-number>
                      <t-input :placeholder="lang.input" v-model="row.value" show-limit-number v-else
                        :maxlength="calcLength(row.key)">
                      </t-input>
                    </template>
                    <t-tree-select :min-collapsed-num="1" v-model="row.value"
                    v-if="row.key === 'product_name'"
                      :data="calcOption(row.key)" :tree-props="treeProps1"
                      multiple clearable :placeholder="lang.select">
                    </t-tree-select>
                    <t-select v-model="row.value" :min-collapsed-num="1" :multiple="calcCurFiled(row.key).type === 'multi_select' ? true : false"
                      v-if="calcCurFiled(row.key).type === 'select' ||
                     calcCurFiled(row.key).type === 'multi_select' && row.key !== 'product_name'">
                      <t-option :value="item.id" :label="item.name" v-for="item in calcCurFiled(row.key).option" :key="item.id">
                      </t-option>
                    </t-select>
                    <!-- type === 'date' -->
                    <template v-if="calcCurFiled(row.key).type === 'date'">
                      <t-date-picker v-if="row.rule === 'equal'" v-model="row.value" allow-input clearable></t-date-picker>
                      <t-date-range-picker allow-input clearable v-if="row.rule === 'interval'" v-model="row.value"></t-date-range-picker>
                      <div class="dynamic-item" v-if="row.rule === 'dynamic'">
                        <t-input-number v-model="row.value.day1" theme="normal" :disabled="row.value.condition1 === 'now'"></t-input-number>
                        <t-select v-model="row.value.condition1" @change="chooseTimeRange($event, row, 'day1')">
                          <t-option :value="item.value" :label="item.label" v-for="item in dayArr" :key="item.value">
                          </t-option>
                        </t-select>
                        <span>{{lang.view_to}}</span>
                        <t-input-number v-model="row.value.day2" theme="normal" :disabled="row.value.condition2 === 'now'"></t-input-number>
                        <t-select v-model="row.value.condition2" @change="chooseTimeRange($event, row, 'day2')">
                          <t-option :value="item.value" :label="item.label" v-for="item in dayArr" :key="item.value">
                          </t-option>
                        </t-select>
                      </div>
                    </template>
                  </template>
                </template>
                <template #op="{row,rowIndex}">
                  <t-icon name="add-circle" class="common-look" @click="addRange"
                  v-if="rowIndex + 1 === viewForm.select_data_range.length"></t-icon>
                  <t-icon name="minus-circle" class="common-look" @click="subRange(rowIndex)"></t-icon>
                </template>
              </t-table>
            </t-form-item>
            <div class="com-f-btn">
              <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
              <t-button theme="default" variant="base" @click="closeView">{{lang.cancel}}</t-button>
            </div>
          </t-form>
        </div>
      </t-dialog>
    </div>
  `,
  data() {
    return {
      id: "", // 视图id
      admin_view_list: [], // 所有视图
      hover: true,
      filedArr: [],
      selectField: [],
      filedModel: false,
      submitLoading: false,
      keywords: "",
      childField: [],
      tempField: [],
      isInit: false,
      filedColumns: [
        {
          colKey: "drag",
          width: 30,
          className: "drag-icon",
        },
        {
          colKey: "name",
          title: "",
          ellipsis: true,
        },
        {
          colKey: "op",
          width: 30,
        },
      ],
      popupProps: {
        overlayClassName: `view-select`,
      },
      // 视图管理
      viewManageDialog: false,
      viewManageForm: {
        type: null,
        choose: null,
      },
      viewManageList: [],
      viewManageChoose: [],
      loading: false,
      viewColumns: [
        {
          // 列拖拽排序必要参数
          colKey: "drag",
          width: 20,
          className: "drag-icon",
        },
        {
          colKey: "name",
          title: lang.view_name,
          ellipsis: true,
          className: "group-column",
        },
        {
          colKey: "status",
          title: lang.isOpen,
          ellipsis: true,
          width: 120,
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 100,
        },
      ],
      delVisible: false,
      curId: "",
      // 新建视图
      viewModel: false,
      rules: {
        name: [
          {
            required: true,
            message: lang.input + lang.view_name,
            type: "error",
          },
        ],
      },
      viewForm: {
        name: "",
        select_field: [],
        data_range_switch: 0,
        select_data_range: [],
      },
      optType: "add",
      viewTit: "",
      viewKeywords: "",
      viewFilter: null,
      viewFiledArr: [],
      viewSelected: [],
      viewRangeData: [],
      allChildView: [], // 范围所有字段
      treeProps: {
        valueMode: "onlyLeaf",
        keys: {
          label: "name",
          value: "key",
          children: "children",
        },
      },
      treeProps1: {
        keys: {
          label: "name",
          value: "id",
        },
      },
      rangeColumns: [
        {
          colKey: "index",
          title: lang.order_index,
          width: 80,
        },
        {
          colKey: "key",
          title: lang.mf_condition,
          ellipsis: true,
          width: 180,
        },
        {
          colKey: "rule",
          title: lang.mf_rule,
          ellipsis: true,
          width: 180,
        },
        {
          colKey: "value",
          title: lang.view_value,
          ellipsis: true,
          className: "value-item",
        },
        {
          colKey: "op",
          title: lang.operation,
          width: 100,
        },
      ],
      // 规则
      viewRule: {
        equal: lang.view_equal,
        not_equal: lang.view_not_equal,
        include: lang.view_include,
        not_include: lang.view_not_include,
        empty: lang.view_empty,
        not_empty: lang.view_not_empty,
        interval: lang.view_interval,
        dynamic: lang.view_dynamic,
      },
      dayArr: [
        {value: "ago", label: lang.view_ago},
        {value: "now", label: lang.view_now},
        {value: "later", label: lang.view_later},
      ],
      isOutEdit: false, // 高级筛选处编辑
      defaultId: "",
      priceTypeArr: [
        "renew_amount",
        "first_payment_amount",
        "base_price",
        "order_amount",
        "order_use_credit",
        "order_refund_amount",
      ],
    };
  },
  computed: {
    isPriceType() {
      return (type) => {
        return this.priceTypeArr.includes(type);
      };
    },
    calcLength() {
      return (key) => {
        switch (key) {
          case "ip":
            return 150;
          case "phone":
            return 20;
          default:
            return 30;
        }
      };
    },
    // 处理已选的数据范围不可选
    calcDisabelViewRangeData() {
      let keyArr = this.viewForm.select_data_range
        .map((item) => item.key)
        .filter((item) => item);
      const temp = this.viewRangeData.map((item) => {
        item.children = item.children.map((el) => {
          if (keyArr.includes(el.key)) {
            el.disabled = true;
          } else {
            el.disabled = false;
          }
          return el;
        });
        return item;
      });
      return temp;
    },
    // 动态生成规则
    calcReg() {
      return (name, min, max) => {
        return [
          {required: true, message: `${lang.input}${name}`, type: "error"},
          {
            pattern: /^[0-9]*$/,
            message: lang.input + `${min}-${max}` + lang.verify1,
            type: "warning",
          },
          {
            validator: (val) => val >= min && val <= max,
            message: lang.input + `${min}-${max}` + lang.verify1,
            type: "warning",
          },
        ];
      };
    },
    calcOption() {
      return (key) => {
        const temp = this.viewRangeData.reduce((all, cur) => {
          all.push(...cur.children);
          return all;
        }, []);
        const option = temp.filter((item) => item.key === key)[0]?.option || [];
        return option;
      };
    },
    // 筛选当前选中的字段，获取rule,type
    calcCurFiled() {
      return (key) => {
        const temp = this.viewRangeData.reduce((all, cur) => {
          all.push(...cur.children);
          return all;
        }, []);
        const type = temp.filter((item) => item.key === key)[0]?.type || "";
        let rule = temp.filter((item) => item.key === key)[0]?.rule || [];
        rule = rule.reduce((all, cur) => {
          all.push({
            value: cur,
            label: this.viewRule[cur],
          });
          return all;
        }, []);
        const option = temp.filter((item) => item.key === key)[0]?.option || [];
        return {
          rule,
          type,
          option: JSON.parse(JSON.stringify(option)),
        };
      };
    },
    // calcViewFiled () {
    //   if (this.optType === 'add') {
    //     return this.viewFiledArr;
    //   } else {
    //     const temp = this.viewFiledArr.map(item => {
    //       item.field.map(el => {
    //         if (this.viewSelected.includes(el.key)) {
    //           el.checked = true;
    //         } else {
    //           el.checked = false;
    //         }
    //         return el;
    //       });
    //       return item;
    //     });
    //     return temp;
    //   }
    // },
    filterField() {
      if (!this.keywords) {
        return this.filedArr;
      } else {
        const temp = JSON.parse(JSON.stringify(this.filedArr));
        return temp.map((item) => {
          item.field = item.field.filter(
            (el) => el.name.indexOf(this.keywords) !== -1
          );
          return item;
        });
      }
    },
    replaceText() {
      return (el) => {
        if (el.name.indexOf(this.keywords) !== -1 && this.keywords !== "") {
          return el.name.replace(
            this.keywords,
            '<span style="color: var(--td-brand-color);">' +
              this.keywords +
              "</span>"
          );
        } else {
          return el.name;
        }
      };
    },
    calcSelectFiled() {
      let tempData = [];
      if (this.filedModel) {
        tempData = this.selectField;
      }
      if (this.viewModel) {
        tempData = this.viewSelected;
      }
      //console.log('tempData', tempData)
      return tempData.reduce((all, cur) => {
        all.push({
          key: cur,
          name: this.childField.filter((item) => item.key === cur)[0]?.name,
        });
        return all;
      }, []);
    },
    calcTit() {
      const curView = this.admin_view_list.filter(
        (item) => item.id === this.id
      )[0]?.name;
      const temp = this.view === "host" ? `「${curView}」` : "";
      return `${lang.field_setting}${temp}`;
    },
  },
  props: {
    view: {
      type: String,
      required: true,
      default: "", // client, order, host, transaction
    },
  },
  // watch: {
  //   'viewForm.select_data_range' (val) {
  //     if (val.length === 0) {
  //       this.viewForm.data_range_switch = 0;
  //     }
  //   },
  // },
  created() {
    this.getViewFiledList();
    // 视图范围
    this.getViewFiledRange();
  },
  methods: {
    /* 新建视图 */
    changeSearch() {
      if (this.viewKeywords) {
        this.viewFilter = (node) => {
          const res = node.data.name.indexOf(this.viewKeywords) != -1;
          return res;
        };
      } else {
        this.viewFilter = null;
      }
    },
    // 另存为视图
    saveAsView() {
      this.filedModel = false;
      this.viewModel = true;
      this.optType = "add";
      this.isOutEdit = false;
      this.viewTit = lang.field_add_view;
      this.viewForm.name = "";
      this.viewForm.select_field = this.viewSelected = JSON.parse(
        JSON.stringify(this.selectField)
      );
    },
    // 新建/管理视图
    chooseView(id) {
      this.isOutEdit = false;
      if (id === "new" || id === "manage") {
        if (id === "new") {
          this.viewModel = true;
          this.optType = "add";
          this.viewTit = lang.field_add_view;
          this.viewForm.select_field = this.viewSelected = ["id"];
          this.viewForm = {
            name: "",
            select_field: ["id"],
            data_range_switch: 0,
            select_data_range: [],
          };
          this.$refs.viewForm.reset();
        }
        if (id === "manage") {
          this.getManageList();
        }
        return;
      }
      this.id = id;
      this.getViewFiledList();
    },
    async submitView({validateResult, firstError}) {
      if (validateResult === true) {
        try {
          if (!this.handelCheckRange()) {
            return;
          }
          this.submitLoading = true;
          const params = JSON.parse(JSON.stringify(this.viewForm));
          if (this.optType === "add") {
            delete params.id;
          }
          // 处理 date 区间的数据
          params.select_data_range = params.select_data_range.map((item) => {
            if (item.rule === "interval") {
              item.value = {
                start: item.value[0],
                end: item.value[1],
              };
            }
            return item;
          });
          params.view = this.view;
          params.select_field = this.viewSelected;
          const res = await addAndEditViewFiled(this.optType, params);
          this.$message.success(res.data.msg);
          this.optType = "";
          this.getViewFiledList();
          this.viewModel = false;
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
      }
    },
    closeView() {
      this.optType = "";
      this.viewModel = false;
    },
    // date 动态的时候
    chooseTimeRange(val, row, name) {
      const index = this.viewForm.select_data_range.findIndex(
        (item) => item.key === row.key
      );
      if (val === "now") {
        this.$set(this.viewForm.select_data_range[index].value, name, null);
      }
    },
    // 校验所选范围是否合规
    handelCheckRange() {
      const bol = this.viewForm.select_data_range.some((item) => {
        if (item.rule === "empty" || item.rule === "not_empty") {
          return false;
        } else {
          return (
            !item.key ||
            !item.rule ||
            (typeof item.value === "object" &&
            item.value !== null &&
            item.value !== undefined
              ? item.value.lengh === 0
              : item.value === "" ||
                item.value === null ||
                item.value === undefined)
          );
        }
      });
      if (bol) {
        this.$message.warning(lang.field_tip6);
        return false;
      } else {
        return true;
      }
    },
    /* 数据范围 */
    addRange() {
      // 判断之前是否有数据为空
      if (!this.handelCheckRange()) {
        return;
      }
      this.viewForm.select_data_range.push({
        key: "",
        rule: "",
        value: [],
      });
    },
    subRange(index) {
      this.viewForm.select_data_range.splice(index, 1);
    },
    // 根据key获取对应的type
    changeKey(val) {
      const curFiled = this.calcCurFiled(val);
      const index = this.viewForm.select_data_range.findIndex(
        (item) => item.key === val
      );
      if (curFiled.type === "input" || curFiled.type === "select") {
        this.$set(this.viewForm.select_data_range[index], "value", null);
      } else {
        this.$set(this.viewForm.select_data_range[index], "value", []);
      }
      this.$set(this.viewForm.select_data_range[index], "rule", "");
    },
    // 切换rule, data类型改变处理value的默认值
    changeRule(val, row) {
      // type 是 date 的需要处理
      const dataTemp = this.allChildView.find((item) => item.key === row.key);
      const index = this.viewForm.select_data_range.findIndex(
        (item) => item.key === row.key
      );
      if (dataTemp.type === "date") {
        if (val === "equal" || val === "empty" || val === "not_empty") {
          this.$set(this.viewForm.select_data_range[index], "value", "");
        } else if (val === "interval") {
          this.$set(this.viewForm.select_data_range[index], "value", []);
        } else if (val === "dynamic") {
          this.$set(this.viewForm.select_data_range[index], "value", {
            day1: null,
            condition1: "ago",
            day2: null,
            condition2: "ago",
          });
        }
      }
    },
    // 启用范围开关
    changeRangeSwitch(val) {
      if (val && this.viewForm.select_data_range.length === 0) {
        this.viewForm.select_data_range.push({
          key: "",
          rule: "",
          value: [],
        });
      }
    },
    handleNumber(val, key) {
      const index = this.viewForm.select_data_range.findIndex(
        (item) => item.key === key
      );
      let max = 0;
      if (key === "id" || key === "client_id") {
        max = 9999999999;
      } else {
        max = 9999999999.99;
      }
      if (val <= 0) {
        num = key === "id" || key === "client_id" ? 1 : 0.0;
      } else if (val >= max) {
        num = max;
      } else {
        num = val;
      }
      this.$set(this.viewForm.select_data_range[index], "value", num);
    },
    // 选择字段
    changeCheck(checkArr, {node}) {
      // 根据顺序选择: 单选没有问题， 点击父级也要同样效果的话需要取 获取 node.data.field 循环
      // if (node.checked) {
      //   this.viewSelected.push(node.value);
      // } else {
      //   const index = this.viewSelected.findIndex(item => item === node.value);
      //   this.viewSelected.splice(index, 1);
      // }
      this.viewSelected = checkArr;
    },
    /* 新建视图 end */
    /* 视图管理 */
    async getManageList() {
      try {
        const res = await getViewList({
          view: this.view,
        });
        const {choose, list, choose_list} = res.data.data;
        this.viewManageDialog = true;
        this.viewManageForm.choose = choose;
        this.viewManageList = list;
        this.viewManageChoose = choose_list;
        this.viewManageForm.type = choose === 0 ? "scan" : "specify";
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    // 修改视图
    async onSubmit({validateResult, firstError}) {
      if (validateResult === true) {
        try {
          this.submitLoading = true;
          const res = await specifyView({
            view: this.view,
            choose: this.viewManageForm.choose,
          });
          this.$message.success(res.data.msg);
          this.getManageList();
          this.getViewFiledList();
          this.submitLoading = false;
        } catch (error) {
          this.submitLoading = false;
          this.$message.error(error.data.msg);
        }
      } else {
        console.log("Errors: ", validateResult);
      }
    },
    // 获取视图范围
    async getViewFiledRange() {
      try {
        if (this.view !== "host" && this.view !== "order") {
          return;
        }
        const res = await getViewRange({
          view: this.view,
        });
        this.viewRangeData = res.data.data.data_range.map((item) => {
          item.key = item.name;
          item.children = item.field;
          delete item.field;
          return item;
        });
        this.allChildView = res.data.data.data_range.reduce((all, cur) => {
          all.push(...cur.children);
          return all;
        }, []);
      } catch (error) {}
    },
    async copyHandler(row) {
      try {
        const res = await copyViewFiled({
          id: row.id,
        });
        this.$message.success(res.data.msg);
        this.getManageList();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    editHandler(row) {
      if (row === undefined) {
        this.isOutEdit = true;
        row = {
          id: this.id,
        };
      } else {
        this.isOutEdit = false;
      }
      this.optType = "update";
      this.viewTit = lang.field_edit_view;
      this.getViewFiledList(row.id);
      this.viewManageDialog = false;
      this.viewTit = lang.field_edit_view;
    },
    deleteHandler(row) {
      this.curId = row.id;
      this.delVisible = true;
    },
    async sureDel() {
      try {
        this.submitLoading = true;
        const res = await deleteViewFiled({id: this.curId});
        this.$message.success(res.data.msg);
        this.submitLoading = false;
        // 删除当前选中的视图，自动切换到默认视图
        if (this.curId === this.id) {
          this.id = this.defaultId;
        }
        this.getManageList();
        this.getViewFiledList();
        this.delVisible = false;
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    // 视图管理排序
    async changeViewManageSort(e) {
      try {
        const temp = e.newData.reduce((all, cur) => {
          all.push(cur.id);
          return all;
        }, []);
        const res = await changeViewFiledOrder({
          id: temp,
          view: this.view,
        });
        this.$message.success(res.data.msg);
        this.getManageList();
        this.getViewFiledList();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    changeViewType(type) {
      try {
        if (type === "scan") {
          this.viewManageForm.choose = 0;
        } else {
          this.viewManageForm.choose = this.viewManageChoose[0]?.id;
        }
      } catch (error) {}
    },
    async changeViewStatus(val, row) {
      try {
        const res = await changeViewFiled({
          id: row.id,
          status: val,
        });
        this.$message.success(res.data.msg);
        // 隐藏当前选中的视图，自动切换到默认视图
        if (row.id === this.id && !val) {
          this.id = this.defaultId;
        }
        this.getManageList();
        this.getViewFiledList();
      } catch (error) {
        this.$message.error(error.data.msg);
        this.getManageList();
      }
    },
    /* 视图管理 end */
    handleFiled() {
      this.optType = "";
      this.filedModel = true;
    },
    async submitField() {
      try {
        this.submitLoading = true;
        const res = await saveViewFiled({
          id: this.id,
          select_field: this.selectField,
        });
        this.submitLoading = false;
        this.filedModel = false;
        this.$message.success(res.data.msg);
        this.getViewFiledList();
      } catch (error) {
        this.submitLoading = false;
        this.$message.error(error.data.msg);
      }
    },
    // 字段拖动
    changeSort({newData}) {
      this.selectField = newData.map((item) => item.key);
    },
    changeViewSort({newData}) {
      this.viewSelected = newData.map((item) => item.key);
    },
    changeField(bol, key) {
      if (bol) {
        this.selectField.push(key);
      } else {
        const index = this.selectField.findIndex((item) => item === key);
        this.selectField.splice(index, 1);
      }
    },
    delField(type, row) {
      if (type === "filed") {
        const index = this.selectField.findIndex((item) => item === row.key);
        this.selectField.splice(index, 1);
        this.handelData();
      } else if (type === "view") {
        const index = this.viewSelected.findIndex((item) => item === row.key);
        this.viewSelected.splice(index, 1);
      }
    },
    async getViewFiledList(val) {
      try {
        const res = await getViewFiled({
          id: val || this.id,
          view: this.view,
        });
        if (this.optType === "update") {
          let {id, name, data_range_switch, select_field, select_data_range} =
            res.data.data;
          // 回填处理 date 区间的数据
          select_data_range = select_data_range.map((item) => {
            if (item.rule === "interval") {
              item.value = [item.value.start, item.value.end];
            }
            return item;
          });
          this.viewForm = {
            id,
            name,
            data_range_switch,
            select_field,
            select_data_range,
          };
          this.viewSelected = select_field;
          this.viewModel = true;
          return;
        }
        const {
          id,
          admin_view_list,
          field,
          select_field,
          select_data_range,
          password_field,
          data_range_switch,
        } = res.data.data;
        this.id = id;
        this.defaultId = admin_view_list.filter(
          (item) => item.default === 1
        )[0]?.id;
        this.admin_view_list = admin_view_list;
        this.filedArr = field || [];
        this.viewFiledArr = JSON.parse(JSON.stringify(field || [])).map(
          (item) => {
            item.key = item.name;
            item.field = item.field.map((el) => {
              if (el.key === "id") {
                el.disabled = true;
              }
              return el;
            });
            return item;
          }
        );
        this.selectField = select_field || [];
        this.childField = this.filedArr.reduce((all, cur) => {
          all.push(...cur.field);
          return all;
        }, []);
        this.handelData();
        let sortArr = [];
        // 排序字段
        switch (this.view) {
          case "client":
            sortArr = [
              "id",
              "reg_time",
              "host_active_num_host_num",
              "client_credit",
              "cost_price",
              "refund_price",
              "withdraw_price",
            ];
            break;
          case "order":
            sortArr = [
              "id",
              "order_amount",
              "client_id",
              "reg_time",
              "pay_time",
            ];
            break;
          case "host":
            sortArr = [
              "id",
              "renew_amount_cycle",
              "due_time",
              "first_payment_amount",
              "active_time",
              "client_id",
              "reg_time",
            ];
            break;
          case "transaction":
            sortArr = [
              "id",
              "amount",
              "transaction_number",
              "order_id",
              "transaction_time",
              "client_id",
              "reg_time",
            ];
            break;
        }
        // 直接返回处理好的表头
        const backColumns = select_field.reduce((all, cur) => {
          const item =
            this.childField.filter((item) => item.key === cur)[0] || [];
          const params = {
            colKey: item.key,
            title: item.name,
            ellipsis: true,
            minWidth: 120,
          };
          if (sortArr.includes(item.key)) {
            params.sortType = "all";
            params.sorter = true;
          }
          if (this.priceTypeArr.includes(item.key)) {
            params.className = "price-type-cell";
          }
          all.push(params);
          return all;
        }, []);
        const customField = select_field.filter(
          (item) =>
            item.indexOf("addon_client_custom_field") !== -1 ||
            item.indexOf("self_defined_field") !== -1
        );

        /*
          backColumns：表头数据
          customField：自定义字段
          isInit：首次渲染
          len：当前视图配置的范围数量
          password_field：密码类型的字段
          defaultId：默认视图ID
          admin_view_list：可选视图
          data_range_switch： 开关
        */
        const globalFiled = this.childField.filter(item => item.is_global === 1)
        this.$emit("changefield", {
          view_id: this.id,
          backColumns,
          customField,
          isInit: this.isInit,
          len: select_data_range.length,
          password_field,
          defaultId: this.defaultId,
          admin_view_list,
          data_range_switch,
          select_field,
          globalFiled
        });
        this.isInit = true;
      } catch (error) {
        console.log("error", error);
        this.$message.error(error.data.msg);
      }
    },
    handelData() {
      this.filedArr = this.filedArr.map((item) => {
        item.field.map((el) => {
          if (this.selectField.includes(el.key)) {
            el.checked = true;
          } else {
            el.checked = false;
          }
          return el;
        });
        return item;
      });
    },
  },
};

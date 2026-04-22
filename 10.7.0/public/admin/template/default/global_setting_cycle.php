{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/global_setting.css">
<div id="content" class="product-notice-manage hasCrumb" v-cloak>
  <com-config>
    <div class="com-crumb">
      <span>{{lang.refund_commodit_management}}</span>
      <t-icon name="chevron-right"></t-icon>
      <a href="product.htm">{{lang.product_list}}</a>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.product_set_text1}}</span>
    </div>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li v-permission="'auth_product_management_notice_setting'">
          <a href="global_setting_notice.htm">{{lang.product_set_text2}}</a>
        </li>
        <li v-permission="'auth_product_management_global_setting_custom'">
          <a :href="`global_setting_custom.htm`">{{lang.product_set_text3}}</a>
        </li>
        <li class="active" v-permission="'auth_product_management_global_setting_cycle'">
          <a href="javascript:;">{{lang.product_set_text4}}</a>
        </li>
        <li v-permission="'auth_product_management_global_setting_os'">
          <a :href="`global_setting_os.htm`">{{lang.product_set_text5}}</a>
        </li>
        <li v-permission="'auth_product_management_global_setting_ratio'">
          <a :href="`global_setting_ratio.htm`">{{lang.product_set_text6}}</a>
        </li>
        <li>
          <a href="global_setting_demand.htm">{{lang.product_set_text126}}</a>
        </li>
        <li v-permission="'auth_product_management_global_setting_other'">
          <a :href="`global_setting_other.htm`">{{lang.product_set_text7}}</a>
        </li>
      </ul>
      <div class="common-tab-content">
        <div class="cycle-seting">
          <span>{{lang.product_set_text27}}</span>
          <t-switch size="medium" :custom-value="[1,0]" v-model="configForm.product_duration_group_presets_open"
            style="margin-left: 10px;">
          </t-switch>
          <t-button style="margin-left: 15px;" @click="saveConfig" :loading="saveLoading">{{lang.hold}}</t-button>
        </div>
        <div class="cycle-box" v-show="configForm.product_duration_group_presets_open === 1">
          <div class="cycle-item">
            <div class="cycle-item-title">
              <h3>{{lang.product_set_text28}}</h3>
              <t-button @click="handelAddCycle" style="margin-left: 15px;">{{lang.product_set_text29}}</t-button>
            </div>
            <t-table hover row-key="id" :loading="loading" :data="cycleList" :columns="cycleColumns">
              <template #name="{row}">
                <span>
                  {{row.name || '--'}}
                </span>
              </template>
              <template #duration_info="{row}">
                <div v-for="item in row.duration_info">{{item.name}}({{item.num}}{{getUnitName(item.unit)}})</div>
              </template>
              <template #ration_info="{row}">
                <span>{{calcDurationName(row) || '--'}}</span>
              </template>
              <template #op="slotProps">
                <t-tooltip :content="lang.product_set_text36" :show-arrow="false" theme="light">
                  <t-icon name="file-copy" color="var(--td-brand-color)" style="margin-right: 10px;"
                    @click="copyCycle(slotProps.row.id)" class="common-look">
                  </t-icon>
                </t-tooltip>
                <t-tooltip :content="lang.product_set_text15" :show-arrow="false" theme="light">
                  <t-icon name="edit-1" color="var(--td-brand-color)" style="margin-right: 10px;"
                    @click="editCycle(slotProps.row)" class="common-look">
                  </t-icon>
                </t-tooltip>
                <t-tooltip :content="lang.product_set_text16" :show-arrow="false" theme="light">
                  <t-icon name="delete" color="var(--td-brand-color)" class="common-look"
                    @click="delCycle(slotProps.row.id)"></t-icon>
                </t-tooltip>
              </template>
            </t-table>
          </div>
          <div class="cycle-item">
            <div class="cycle-item-title">
              <h3>{{lang.product_set_text49}}</h3>
            </div>
            <div style="display: flex;align-items: center;">
              <span style="margin-right: 10px;">{{lang.product_set_text50}}:</span>
              <t-radio-group v-model="configForm.product_duration_group_presets_apply_range">
                <t-radio :value="0">
                  <t-tooltip :content="lang.product_set_text53" :show-arrow="false" theme="light">
                    {{lang.product_set_text51}}
                    <t-icon name="help-circle" color="var(--td-brand-color)" style="margin-left: 5px;">
                    </t-icon>
                  </t-tooltip>
                </t-radio>
                <t-radio :value="1">
                  <t-tooltip :content="lang.product_set_text54" :show-arrow="false" theme="light">
                    {{lang.product_set_text52}}
                    <t-icon name="help-circle" color="var(--td-brand-color)" style="margin-left: 5px;">
                    </t-icon>
                  </t-tooltip>
              </t-radio-group>
              <t-button style="margin-left: 15px;" @click="saveConfig" :loading="saveLoading">{{lang.hold}}</t-button>
            </div>
            <div v-if="configForm.product_duration_group_presets_apply_range === 0"
              style="margin-top: 10px; width: 400px; display: flex;align-items: center;">
              <span style="flex-shrink: 0; margin-right: 5px;">{{lang.product_set_text55}}:</span>
              <t-select v-model="configForm.product_duration_group_presets_default_id"
                :placeholder="lang.product_set_text55">
                <t-option v-for="item in cycleList" :value="item.id" :label="item.name" :key="item.id"></t-option>
              </t-select>
            </div>
            <div v-if="configForm.product_duration_group_presets_apply_range === 1">
              <div class="table-top">
                <t-button @click="handelAddPreset">{{lang.product_set_text56}}</t-button>
                <!-- <t-input v-model="keywords" style="width: 300px;" :placeholder="lang.product_set_text57"
                  @keypress.enter.native="getLinkList">
                  <template #suffix-icon>
                    <t-icon :style="{ cursor: 'pointer' }" name="search" @click="getLinkList"></t-icon>
                  </template>
                </t-input> -->
              </div>
              <t-table hover row-key="id" :loading="loading2" :data="linkList" :columns="linkColumns">
                <template #servers="{row}">
                  <div v-for="item in row.servers">{{item.server_name}}({{item.server_id}})</div>
                </template>
                <template #name="{row}">
                  <div>{{row.name || '--'}}</div>
                </template>
                <template #op="slotProps">
                  <t-tooltip :content="lang.product_set_text15" :show-arrow="false" theme="light">
                    <t-icon name="edit-1" color="var(--td-brand-color)" style="margin-right: 10px;"
                      @click="editLink(slotProps.row)" class="common-look">
                    </t-icon>
                  </t-tooltip>
                  <t-tooltip :content="lang.product_set_text16" :show-arrow="false" theme="light">
                    <t-icon name="delete" color="var(--td-brand-color)" class="common-look"
                      @click="delLink(slotProps.row.gid)"></t-icon>
                  </t-tooltip>
                </template>
              </t-table>
            </div>

          </div>
        </div>
      </div>
    </t-card>
    <!-- 新建周期配置组弹窗 -->
    <t-dialog :header="editId ? lang.product_set_text38 : lang.product_set_text29" :visible.sync="editCycleDialog"
      :footer="false" width="650" @closed="cycleDiaClose">
      <t-form :rules="cycleRules" ref="cycleDialog" :data="cycleFormData" @submit="onSubmit" :label-width="120"
        reset-type="initial" label-align="top">
        <t-form-item :label="lang.product_set_text39" name="name">
          <t-input v-model="cycleFormData.name" :placeholder="lang.product_set_text39"></t-input>
        </t-form-item>
        <t-form-item name="ratio_open">
          <template #label>
            <span>{{lang.product_set_text40}}</span>
            <t-switch :custom-value="[1,0]" v-model="cycleFormData.ratio_open" style="margin-left: 10px;">
            </t-switch>
          </template>
          <t-table ref="tableRef" row-key="key" :columns="addCycleColumns" :data="cycleFormData.durations" bordered>
            <template #name="{row}">
              <t-input v-model="row.name" :placeholder="lang.product_set_text41" :readonly="!row.editable"></t-input>
            </template>
            <template #num="{row}">
              <t-input-number style="width: 100%;" v-model="row.num" :min="0" :step="1" :decimal-places="0"
                :readonly="!row.editable" theme="normal" :placeholder="lang.product_set_text42">
              </t-input-number>
            </template>
            <template #ratio="{row}">
              <t-input-number style="width: 100%;" v-model="row.ratio" :min="0" :decimal-places="2"
                :readonly="!row.editable" theme="normal" :placeholder="lang.product_set_text48">
              </t-input-number>
            </template>
            <template #unit="{row}">
              <t-select v-model="row.unit" :options="unitOptions" :readonly="!row.editable"
                :placeholder="lang.select + lang.product_set_text43">
              </t-select>
            </template>

            <template #op="slotProps">
              <t-tooltip v-if="slotProps.row.editable" :content="lang.hold" :show-arrow="false" theme="light">
                <t-icon name="save" color="var(--td-brand-color)" style="margin-right: 10px;"
                  @click="saveDuration(slotProps)" class="common-look">
                </t-icon>
              </t-tooltip>
              <t-tooltip v-if="!slotProps.row.editable" :content="lang.edit" :show-arrow="false" theme="light">
                <t-icon name="edit-1" color="var(--td-brand-color)" style="margin-right: 10px;"
                  @click="editDuration(slotProps)" class="common-look">
                </t-icon>
              </t-tooltip>
              <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
                <t-icon name="delete" color="var(--td-brand-color)" class="common-look"
                  @click="delDuration(slotProps)"></t-icon>
              </t-tooltip>
            </template>
            <template #footer-summary>
              <div style="cursor: pointer; color:var(--td-brand-color);" class="t-table__row-filter-inner"
                @click="addDuration">
                <t-icon name="add" color="var(--td-brand-color)" style="margin-right: 10px;" class="common-look">
                </t-icon> {{lang.product_set_text44}}
              </div>
            </template>
          </t-table>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.client_custom_label13}}</t-button>
          <t-button theme="default" variant="base"
            @click="editCycleDialog=false">{{lang.client_custom_label14}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 删除提示框 -->
    <t-dialog theme="warning" :header="lang.product_set_text37" :close-btn="false" :visible.sync="delVisible"
      class="delDialog">
      <template slot="footer">
        <t-button theme="primary" @click="sureDelCycle" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
    <t-dialog theme="warning" :header="lang.product_set_text62" :close-btn="false" :visible.sync="delLinkVisible"
      class="delDialog">
      <template slot="footer">
        <t-button theme="primary" @click="sureDelLink" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delLinkVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
    <!-- 编辑接口弹窗 -->
    <t-dialog :header="lang.product_set_text60" :visible.sync="editLinkDialog" :footer="false" width="650"
      @closed="linkDiaClose">
      <t-form :rules="cycleRules" ref="linkDialog" :data="linkFormData" @submit="linkSubmit" :label-width="120"
        reset-type="initial" label-align="top">
        <t-form-item :label="lang.product_set_text61" name="server_ids">
          <t-select v-model="linkFormData.server_ids" :placeholder="lang.product_set_text60" multiple clearable
            :min-collapsed-num="2">
            <t-option v-for="item in serverOptions" :value="item.id" :label="item.name" :key="item.id"></t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.product_set_text59" name="gid">
          <t-select v-model="linkFormData.gid" :placeholder="lang.product_set_text59" clearable :min-collapsed-num="2">
            <t-option v-for="item in cycleList" :value="item.id" :label="item.name" :key="item.id"></t-option>
          </t-select>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.client_custom_label13}}</t-button>
          <t-button theme="default" variant="base"
            @click="editLinkDialog=false">{{lang.client_custom_label14}}</t-button>
        </div>
      </t-form>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/global_setting_cycle.js"></script>
{include file="footer"}

{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/product.css">
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
        <li class="active" v-permission="'auth_product_management_global_setting_custom'">
          <a href="javascript:;">{{lang.product_set_text3}}</a>
        </li>
        <li v-permission="'auth_product_management_global_setting_cycle'">
          <a :href="`global_setting_cycle.htm`">{{lang.product_set_text4}}</a>
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
        <h2 class="top-tit">
          <div class="flex">
            {{lang.product_set_text3}}<span class="des">{{lang.product_set_text10}}
          </div>
          <div class="flex">
            <t-button @click="saveConfig" :loading="saveLoading">{{lang.hold}}</t-button>
          </div>
          </span>
        </h2>
        <t-tabs v-model="tab" placement="left" style="margin-top: 20px;" @change="handleTabChange">
          <t-tab-panel value="1" :label="lang.product_set_text8">
            <div class="product-self" style="margin-left: 20px;">
              <div class="material-set">
                <span style="margin-right: 10px;">{{lang.product_set_text17}}:</span>
                <t-radio-group v-model="configForm.self_defined_field_apply_range">
                  <t-radio :value="0">{{lang.product_set_text11}}</t-radio>
                  <t-radio :value="1">{{lang.product_set_text12}}</t-radio>
                </t-radio-group>
              </div>
              <div class="material-top" v-show="configForm.self_defined_field_apply_range === 1">
                <div></div>
                <t-button @click="handADDmaterial">{{lang.client_custom_label3}}</t-button>
              </div>
              <t-table hover row-key="id" :loading="loading" :data="list" :columns="columns" drag-sort="row-handler"
                @drag-sort="onDragSort" v-show="configForm.self_defined_field_apply_range === 1">
                <template #drag="{row}">
                  <t-icon name="move" style="cursor: move;"></t-icon>
                </template>
                <template #title-slot-global>
                  {{lang.product_set_text133}}
                  <t-tooltip placement="top" :content="lang.product_set_text137" :show-arrow="false" theme="light">
                    <t-icon name="help-circle" size="16px" style="margin-left: 0;"/>
                  </t-tooltip>
                </template>
                <template #is_global="{row}">
                  <t-switch :value="row.is_global" :custom-value="[1, 0]" :disabled="isAgent" @change="changeIsGlobal(row)"></t-switch>
                </template>
                <template #field_name="{row}">
                  <span>
                    <span class="error-text" v-if="row.is_required === 1">*</span>
                    {{row.field_name}}
                  </span>
                </template>
                <template #product_group="{row}">
                  <div style="max-width: 230px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                    <template v-if="row.product_group.length > 0">
                      <t-tooltip :show-arrow="false" theme="light" placement="top-right">
                        <span v-for="(item,index) in row.product_group">{{item.first_group_name}}/{{item.name}}
                          <span v-if="index < row.product_group.length - 1">、</span>
                        </span>
                        <template #content>
                          <span v-for="item in row.product_group">{{item.first_group_name}}/{{item.name}} <br></span>
                        </template>
                        </t-icon>
                      </t-tooltip>
                    </template>
                    <span v-else>--</span>
                  </div>
                </template>

                <template #field_type="{row}">
                  <t-tooltip :content="row.field_option" v-if="row.field_type === 'dropdown'" theme="light">
                    <span>{{calcTypeText(row)}}</span>
                  </t-tooltip>
                  <span v-if="row.field_type !== 'dropdown'">{{calcTypeText(row)}}</span>
                </template>
                <template #description="{row}">
                  <span>{{row.description || '--'}}</span>
                </template>
                <template #regexpr="{row}">
                  <span>{{row.regexpr || '--'}}</span>
                </template>
                <template #watch="{row}">
                  <span>
                    {{ calcWatch(row) || '--' }}
                  </span>
                </template>
                <template #op="slotProps">
                  <t-tooltip :content="lang.product_set_text13" :show-arrow="false" theme="light">
                    <t-icon name="link" color="var(--td-brand-color)" style="margin-right: 10px;"
                      @click="linkProduct(slotProps.row)" class="common-look" :class="{'server-disabled': isAgent}">
                    </t-icon>
                  </t-tooltip>
                  <t-tooltip :content="lang.product_set_text15" :show-arrow="false" theme="light">
                    <t-icon name="edit-1" color="var(--td-brand-color)" style="margin-right: 10px;"
                      @click="edit(slotProps.row)" class="common-look" :class="{'server-disabled': isAgent}">
                    </t-icon>
                  </t-tooltip>
                  <t-tooltip :content="lang.product_set_text16" :show-arrow="false" theme="light">
                    <t-icon name="delete" color="var(--td-brand-color)" class="common-look"
                      :class="{'server-disabled': isAgent}" @click="deletes(slotProps.row.id)"></t-icon>
                  </t-tooltip>
                </template>
              </t-table>
            </div>
          </t-tab-panel>
          <t-tab-panel value="2" :label="lang.product_set_text9">
            <div class="product-self" style="margin-left: 20px;">
              <div class="material-set">
                <span style="margin-right: 10px;">{{lang.product_set_text17}}:</span>
                <t-radio-group v-model="configForm.custom_host_name_apply_range">
                  <t-radio :value="0">{{lang.product_set_text11}}</t-radio>
                  <t-radio :value="1">{{lang.product_set_text12}}</t-radio>
                </t-radio-group>
              </div>

              <div class="material-top" v-show="configForm.custom_host_name_apply_range === 1">
                <div></div>
                <t-button @click="handAddHostName">{{lang.product_set_text24}}</t-button>
              </div>
              <t-table hover row-key="id" :loading="loading" :data="hostList" :columns="hostColumns"
                v-show="configForm.custom_host_name_apply_range === 1">

                <template #custom_host_name_prefix="{row}">
                  <span>
                    {{row.custom_host_name_prefix || '--'}}
                  </span>
                </template>
                <template #product_group="{row}">
                  <div style="max-width: 230px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                    <template v-if="row.product_group.length > 0">
                      <t-tooltip :show-arrow="false" theme="light" placement="top-right">
                        <span v-for="(item,index) in row.product_group">{{item.first_group_name}}/{{item.name}}
                          <span v-if="index < row.product_group.length - 1">、</span>
                        </span>
                        <template #content>
                          <span v-for="item in row.product_group">{{item.first_group_name}}/{{item.name}} <br></span>
                        </template>
                        </t-icon>
                      </t-tooltip>
                    </template>
                    <span v-else>--</span>
                  </div>
                </template>

                <template #custom_host_name_string_allow="{row}">
                  <span>
                    <span v-for="(item,index) in row.custom_host_name_string_allow">
                      <span>{{getStringAllowLabel(item)}}</span>
                      <span v-if="index < row.custom_host_name_string_allow.length - 1">、</span>
                    </span>
                  </span>
                </template>
                <template #description="{row}">
                  <span>{{row.description || '--'}}</span>
                </template>
                <template #regexpr="{row}">
                  <span>{{row.regexpr || '--'}}</span>
                </template>
                <template #watch="{row}">
                  <span>
                    {{ calcWatch(row) || '--' }}
                  </span>
                </template>
                <template #op="slotProps">
                  <t-tooltip :content="lang.product_set_text13" :show-arrow="false" theme="light">
                    <t-icon name="link" color="var(--td-brand-color)" style="margin-right: 10px;"
                      @click="linkHostName(slotProps.row)" class="common-look" :class="{'server-disabled': isAgent}">
                    </t-icon>
                  </t-tooltip>
                  <t-tooltip :content="lang.product_set_text15" :show-arrow="false" theme="light">
                    <t-icon name="edit-1" color="var(--td-brand-color)" style="margin-right: 10px;"
                      @click="editHostName(slotProps.row)" class="common-look" :class="{'server-disabled': isAgent}">
                    </t-icon>
                  </t-tooltip>
                  <t-tooltip :content="lang.product_set_text16" :show-arrow="false" theme="light">
                    <t-icon name="delete" color="var(--td-brand-color)" class="common-look"
                      :class="{'server-disabled': isAgent}" @click="deletesHostName(slotProps.row.id)"></t-icon>
                  </t-tooltip>
                </template>
              </t-table>
            </div>
          </t-tab-panel>

        </t-tabs>
      </div>
      <!-- 删除提示框 -->
      <t-dialog theme="warning" :header="lang.client_custom_label6" :close-btn="false" :visible.sync="delVisible"
        class="delDialog">
        <template slot="footer">
          <t-button theme="primary" @click="sureDelUser" :loading="submitLoading">{{lang.sure}}</t-button>
          <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
        </template>
      </t-dialog>
      <!-- 创建字段弹窗 -->
      <t-dialog :header="editId ? lang.client_custom_label7 : lang.client_custom_label8" :visible.sync="materialVisble"
        :footer="false" width="650" @closed="materialDiaClose">
        <t-form :rules="rules" ref="userDialog" label-align="top" :data="formData" @submit="onSubmit" :label-width="120"
          reset-type="initial">
          <t-form-item name="field_name">
            <template slot="label">
              <span class="form-flex-label">
                <span>{{lang.client_custom_label9}}</span>
                <t-checkbox v-model="formData.is_required"
                  v-if="formData.field_type !== 'explain'">{{lang.client_custom_label21}}</t-checkbox>
              </span>
            </template>
            <t-input v-model="formData.field_name" :placeholder="`${lang.client_custom_label9}${multiliTip}`"></t-input>
          </t-form-item>
          <t-form-item :label="lang.client_custom_label10" name="field_type">
            <t-select v-model="formData.field_type" :placeholder="lang.client_custom_label10"
              :disabled="editId !== null">
              <t-option v-for="ele in typeList" :value="ele.value" :label="ele.label" :key="ele.value">
              </t-option>
            </t-select>
          </t-form-item>
          <t-form-item :label="lang.client_custom_label12" :help="lang.client_custom_label11" name="field_option"
            v-if="formData.field_type === 'dropdown'">
            <t-input v-model="formData.field_option" :placeholder="lang.client_custom_label11"></t-input>
          </t-form-item>
          <t-form-item :label="lang.client_custom_label40" name="explain_content"
            v-if="formData.field_type === 'explain'">
            <t-textarea v-model="formData.explain_content" :placeholder="`${lang.client_custom_label41}${multiliTip}`"
              :autosize="{ minRows: 5, maxRows: 10 }">
            </t-textarea>
          </t-form-item>
          <template v-if="formData.field_type !== 'explain'">
            <t-form-item :label="lang.client_custom_label23">
              <t-input v-model="formData.description"
                :placeholder="`${lang.client_custom_label23}${multiliTip}`"></t-input>
            </t-form-item>
            <t-form-item :label="lang.client_custom_label24" :help="lang.client_custom_label25">
              <t-input v-model="formData.regexpr" :placeholder="lang.client_custom_label33"></t-input>
            </t-form-item>
          </template>
          <t-form-item :label="lang.client_custom_label30">
            <div style="display: flex; flex-wrap: wrap; column-gap: 10px; row-gap: 10px;">
              <t-checkbox v-model="formData.show_order_page"
                :disabled="formData.field_type === 'explain'">{{lang.client_custom_label26}}</t-checkbox>
              <template v-if="formData.field_type !== 'explain'">
                <t-checkbox v-model="formData.show_order_detail"
                  v-if="formData.field_type !=='password' && formData.field_type !=='link'">{{lang.client_custom_label27}}</t-checkbox>
                <t-checkbox v-model="formData.show_client_host_detail">{{lang.client_custom_label28}}</t-checkbox>
                <t-checkbox v-model="formData.show_admin_host_detail">{{lang.client_custom_label29}}</t-checkbox>
                <t-checkbox v-model="formData.show_client_host_list">{{lang.client_custom_label34}}</t-checkbox>
                <t-checkbox v-model="formData.show_admin_host_list">{{lang.client_custom_label38}}</t-checkbox>
              </template>
            </div>
          </t-form-item>
          <!-- 2025-11-10 新增全局 -->
          <t-form-item :label="lang.product_set_text133" v-if="editId ? false : true">
            <t-switch v-model="formData.is_global" :custom-value="[1, 0]" :disabled="isAgent"></t-switch>
          </t-form-item>
          <div class="com-f-btn">
            <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.client_custom_label13}}</t-button>
            <t-button theme="default" variant="base"
              @click="materialVisble=false">{{lang.client_custom_label14}}</t-button>
          </div>
        </t-form>
      </t-dialog>
      <!-- 关联商品分组弹窗 -->
      <t-dialog :header="lang.product_set_text13" :visible.sync="linkProductVisble" :footer="false" width="550">
        <t-form :data="linkFormData" @submit="sureLinkPro" :label-width="120" reset-type="initial"
          v-if="linkProductVisble">
          <t-form-item :rules="[{ required: true }]" :label="lang.product_set_text14" name="product_group_id">
            <com-tree-select :is-only-group="true" :show-all="true" :multiple="true"
              :value="linkFormData.product_group_id" @choosepro="choosePro">
            </com-tree-select>
          </t-form-item>
          <div class="com-f-btn">
            <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.client_custom_label13}}</t-button>
            <t-button theme="default" variant="base"
              @click="linkProductVisble=false">{{lang.client_custom_label14}}</t-button>
          </div>
        </t-form>
      </t-dialog>
      <!-- 创建自定义主机名弹窗 -->
      <t-dialog :header="editId ? lang.product_set_text25 : lang.product_set_text24" :visible.sync="HostNameVisble"
        :footer="false" width="650" @closed="hostNameDiaClose">
        <t-form :rules="hostNameRules" ref="hostFormDialog" label-align="top" :data="hostNameForm"
          @submit="hostNameSubmit" :label-width="120" reset-type="initial">
          <t-form-item :label="lang.product_custom_prefix" :help="lang.product_custom_tip4"
            name="custom_host_name_prefix">
            <t-input v-model="hostNameForm.custom_host_name_prefix" :disabled="isAgent"
              :placeholder="`${lang.input}${lang.product_custom_prefix}`" :maxlength="10" show-limit-number></t-input>
          </t-form-item>
          <t-form-item :label="lang.product_custom_string" :help="lang.product_custom_tip5"
            name="custom_host_name_string_allow">
            <t-checkbox-group v-model="hostNameForm.custom_host_name_string_allow" :disabled="isAgent">
              <t-checkbox key="number" value="number">{{lang.product_custom_num}}</t-checkbox>
              <t-checkbox key="upper" value="upper">{{lang.product_custom_upper}}</t-checkbox>
              <t-checkbox key="lower" value="lower">{{lang.product_custom_lower}}</t-checkbox>
            </t-checkbox-group>
          </t-form-item>
          <t-form-item :label="lang.product_custom_length" :help="lang.product_custom_tip6"
            name="custom_host_name_string_length">
            <t-input-number style="width: 100%;" v-model="hostNameForm.custom_host_name_string_length"
              :disabled="isAgent" :placeholder="`${lang.input}${lang.product_custom_length}`" :min="5" :max="50"
              theme="normal" :decimal-places="0" @blur="handleLength">
            </t-input-number>
          </t-form-item>
          <div class="com-f-btn">
            <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.client_custom_label13}}</t-button>
            <t-button theme="default" variant="base"
              @click="HostNameVisble=false">{{lang.client_custom_label14}}</t-button>
          </div>
        </t-form>
      </t-dialog>
      <!-- 删除自定义主机名弹窗 -->
      <t-dialog theme="warning" :header="lang.product_set_text26" :close-btn="false" :visible.sync="hostNameDelVisible"
        class="delDialog">
        <template slot="footer">
          <t-button theme="primary" @click="sureDelHostName" :loading="submitLoading">{{lang.sure}}</t-button>
          <t-button theme="default" @click="hostNameDelVisible=false">{{lang.cancel}}</t-button>
        </template>
      </t-dialog>
      <!-- 全局自定义字段切换弹窗 -->
      <t-dialog :header="lang.product_set_text134" :close-btn="false" :visible.sync="confirmChange"
        class="delDialog">
        <t-checkbox v-model="confirmData.remove">{{lang.product_set_text135}}</t-checkbox>
        <template slot="footer">
          <t-button theme="primary" @click="sureChange" :loading="submitLoading">{{lang.sure}}</t-button>
          <t-button theme="default" @click="confirmChange=false">{{lang.cancel}}</t-button>
        </template>
      </t-dialog>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/comTreeSelect/comTreeSelect.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/global_setting_custom.js"></script>
{include file="footer"}

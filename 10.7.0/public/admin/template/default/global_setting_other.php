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
        <li class="active" v-permission="'auth_product_management_global_setting_other'">
          <a href="javascript:;">{{lang.product_set_text7}}</a>
        </li>
      </ul>
      <div class="common-tab-content">
        <div class="cycle-box">
          <div class="cycle-item">
            <!-- <div style="display: flex;align-items: center;margin: 20px 0;">
              <span style="margin-right: 10px;">{{lang.product_set_text105}}:</span>
              <com-tree-select style="width: 360px;" @choosepro="choosePro" :multiple="true" :value="product_ids">
              </com-tree-select>
            </div> -->
            <div style="display: flex; align-items: center;">
              <span style="margin-right: 10px;">{{lang.product_set_text96}}:</span>
              <t-switch size="medium" :custom-value="[1,0]" v-model="configForm.product_global_show_base_info">
              </t-switch>
            </div>
            <div class="tip-s">{{lang.product_set_text97}}</div>
            <div style="display: flex;align-items: center;margin: 20px 0;">
              <span style="margin-right: 10px;">{{lang.product_set_text93}}:</span>
              <t-radio-group v-model="configForm.product_global_renew_rule">
                <t-radio :value="0" :label="lang.product_set_text94"></t-radio>
                <t-radio :value="1" :label="lang.product_set_text95"></t-radio>
              </t-radio-group>
            </div>
            <div style="display: flex;align-items: center;margin: 20px 0 0 0;">
              <span style="margin-right: 10px;">{{lang.product_due_tip3}}:</span>
              <t-switch size="medium" :custom-value="[1,0]" style="margin:0 10px;"
                v-model="configForm.auto_renew_in_advance"></t-switch>
              <template v-if="configForm.auto_renew_in_advance == 1">
                <t-input-number theme="normal" :min="0" :decimal-places="0" style="width: 130px;"
                  v-model="configForm.auto_renew_in_advance_num">
                </t-input-number>
                <t-select v-model="configForm.auto_renew_in_advance_unit" style="width: 80px;">
                  <t-option value="minute" :label="lang.debug_minutes"></t-option>
                  <t-option value="hour" :label="lang.hour"></t-option>
                  <t-option value="day" :label="lang.product_set_text34"></t-option>
                </t-select>
              </template>
            </div>
            <div class="tip-s">{{lang.product_due_tip5}}</div>
            <div style="display: flex;align-items: center;margin: 20px 0;">
              <span style="margin-right: 10px;">{{lang.product_set_text106}}:</span>
              <t-switch size="medium" :custom-value="[1,0]" v-model="configForm.product_overdue_not_delete_open">
              </t-switch>
              <com-tree-select v-show="configForm.product_overdue_not_delete_open === 1"
                style="width: 360px; margin-left: 20px;" @choosepro="chooseDelPro" :multiple="true"
                :value="configForm.product_overdue_not_delete_product_ids">
              </com-tree-select>
            </div>
            <div style="display: flex;align-items: center;margin: 20px 0;">
              <span style="margin-right: 10px;">{{lang.product_set_text107}}:</span>
              <t-switch size="medium" :custom-value="[1,0]" v-model="configForm.host_sync_due_time_open">
              </t-switch>
              <template v-if="configForm.host_sync_due_time_open">
                <t-radio-group style="margin-left: 20px;" v-model="configForm.host_sync_due_time_apply_range">
                  <t-radio :value="0" :label="lang.product_set_text108"></t-radio>
                  <t-radio :value="1" :label="lang.product_set_text109"></t-radio>
                </t-radio-group>
                <com-tree-select :agent="true" v-show="configForm.host_sync_due_time_apply_range === 1"
                  style="width: 360px; margin-left: 20px;" @choosepro="chooseAngetPro" :multiple="true"
                  :value="configForm.host_sync_due_time_product_ids">
                </com-tree-select>
              </template>
            </div>
            <t-button style="margin-top: 20px;" @click="saveConfig" :loading="saveLoading">{{lang.hold}}</t-button>
          </div>
        </div>
      </div>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/components/comTreeSelect/comTreeSelect.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/global_setting_other.js"></script>
{include file="footer"}

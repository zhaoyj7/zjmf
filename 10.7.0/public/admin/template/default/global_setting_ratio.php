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
          <a :href="`global_setting_notice.htm`">{{lang.product_set_text2}}</a>
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
        <li class="active" v-permission="'auth_product_management_global_setting_ratio'">
          <a href="javascript:;">{{lang.product_set_text6}}</a>
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
          <span>{{lang.product_set_text86}}</span>
          <t-switch size="medium" :custom-value="[1,0]" v-model="configForm.product_new_host_renew_with_ratio_open"
            style="margin-left: 10px;">
          </t-switch>
          <t-button style="margin-left: 15px;" @click="saveConfig" :loading="saveLoading">{{lang.hold}}</t-button>
        </div>
        <div class="cycle-box" v-show="configForm.product_new_host_renew_with_ratio_open === 1">
          <div class="cycle-item">
            <div style="display: flex;align-items: center;margin: 20px 0;">
              <span style="margin-right: 10px;">{{lang.product_set_text87}}:</span>
              <t-radio-group v-model="configForm.product_new_host_renew_with_ratio_apply_range">
                <t-radio :value="2" :label="lang.product_set_text88"></t-radio>
                <t-radio :value="1" :label="lang.product_set_text89"></t-radio>
                <t-radio :value="0" :label="lang.product_set_text90"></t-radio>
              </t-radio-group>
            </div>
            <div v-if="configForm.product_new_host_renew_with_ratio_apply_range === 2"
              style="margin-top: 10px; width: 400px; display: flex;align-items: center;">
              <span style="flex-shrink: 0; margin-right: 5px;">{{lang.product_set_text91}}:</span>
              <com-tree-select :is-only-group="true" :show-all="true" :multiple="true"
                :value="configForm.product_new_host_renew_with_ratio_apply_range_2" @choosepro="choosePro">
              </com-tree-select>
            </div>
            <div v-if="configForm.product_new_host_renew_with_ratio_apply_range === 1">
              <span style="flex-shrink: 0; margin-right: 5px;">{{lang.product_set_text92}}:</span>
              <t-tree-select v-model="configForm.product_new_host_renew_with_ratio_apply_range_1" :min-collapsed-num="1"
                multiple filterable clearable :popupProps="popupProps" :data="serverGroupList" :tree-props="treeProps"
                style="width: 400px">
                <template #panel-top-content>
                  <t-checkbox v-model="checkAll" @change="chooseAll"
                    class="tree-check-all">{{lang.check_all}}</t-checkbox>
                </template>
              </t-tree-select>
            </div>

          </div>
        </div>
      </div>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/comTreeSelect/comTreeSelect.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/global_setting_ratio.js"></script>
{include file="footer"}

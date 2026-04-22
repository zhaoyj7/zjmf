{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/product.css">
<div id="content" class="hasCrumb" v-cloak>
  <com-config>
    <!-- crumb -->
    <div class="com-crumb">
      <span>{{lang.refund_commodit_management}}</span>
      <t-icon name="chevron-right"></t-icon>
      <a href="product.htm">{{lang.product_list}}</a>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.product_custom_name}}</span>
    </div>
    <t-card class="product-container com-sidebar-box">
      <div class="left-box">
        <ul class="common-tab">
          <li class="all">{{lang.auth_all}}</li>
          <li v-permission="'auth_product_detail_basic_info_view'">
            <a :href="`product_detail.htm?id=${id}`">{{lang.basic_info}}</a>
          </li>
          <li v-permission="'auth_product_detail_server_view'">
            <a :href="`product_api.htm?id=${id}`">{{lang.interface_manage}}</a>
          </li>
          <li v-permission="'auth_product_detail_custom_field_view'">
            <a :href="`product_self_field.htm?id=${id}`">{{lang.product_selef_text1}}</a>
          </li>
          <li class="active" v-permission="'auth_product_detail_custom_host_name'">
            <a>{{lang.product_custom_name}}</a>
          </li>
        </ul>
      </div>
      <div class="product-custom-name box">
        <t-form :data="customForm" ref="customForm" @submit="submitCustom" :rules="rules" :label-width="150">
          <t-form-item :label="lang.product_custom_host">
            <t-switch size="large" :custom-value="[1,0]" :disabled="isAgent"
              v-model="customForm.custom_host_name"></t-switch>
            <t-tooltip :content="lang.product_custom_tip1 + '\n' + lang.product_custom_tip2 + '\n' + lang.product_custom_tip3" :show-arrow="false" theme="light" placement="top-left" overlay-class-name='custom-name-pup'>
              <t-icon name="help-circle"></t-icon>
            </t-tooltip>
          </t-form-item>
          <template v-if="customForm.custom_host_name">
            <t-form-item :label="lang.product_custom_prefix" name="custom_host_name_prefix">
              <t-input v-model="customForm.custom_host_name_prefix" :disabled="isAgent" :placeholder="`${lang.input}${lang.product_custom_prefix}`" :maxlength="10" show-limit-number></t-input>
              <span class="s-tip">{{lang.product_custom_tip4}}</span>
            </t-form-item>
            <t-form-item :label="lang.product_custom_string" name="custom_host_name_string_allow">
              <t-checkbox-group v-model="customForm.custom_host_name_string_allow" :disabled="isAgent">
                <t-checkbox key="number" value="number">{{lang.product_custom_num}}</t-checkbox>
                <t-checkbox key="upper" value="upper">{{lang.product_custom_upper}}</t-checkbox>
                <t-checkbox key="lower" value="lower">{{lang.product_custom_lower}}</t-checkbox>
              </t-checkbox-group>
              <span class="s-tip">{{lang.product_custom_tip5}}</span>
            </t-form-item>
            <t-form-item :label="lang.product_custom_length" name="custom_host_name_string_length">
              <t-input-number v-model="customForm.custom_host_name_string_length" :disabled="isAgent" :placeholder="`${lang.input}${lang.product_custom_length}`" :min="5" :max="50" theme="normal" :decimal-places="0" @blur="handleLength">
              </t-input-number>
              <span class="s-tip">{{lang.product_custom_tip6}}</span>
            </t-form-item>
          </template>
          <div class="com-f-btn">
            <t-button theme="primary" type="submit" :loading="submitLoading" :disabled="isAgent">{{lang.hold}}</t-button>
          </div>
        </t-form>
      </div>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/product_custom_name.js"></script>
{include file="footer"}

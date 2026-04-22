{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/global_setting.css">
<div id="content" class="product-notice-demand hasCrumb" v-cloak>
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
        <li v-permission="'auth_product_management_global_setting_ratio'">
          <a :href="`global_setting_ratio.htm`">{{lang.product_set_text6}}</a>
        </li>
        <li class="active">
          <a href="javascript:;">{{lang.product_set_text126}}</a>
        </li>
        <li v-permission="'auth_product_management_global_setting_other'">
          <a :href="`global_setting_other.htm`">{{lang.product_set_text7}}</a>
        </li>
      </ul>
      <div class="common-tab-content">
        <t-form :rules="rules" :data="formData" ref="timeForm" @submit="onSubmit">
          <t-form-item :label="lang.product_set_text127" name="grace_time">
            <t-form-item name="grace_time">
              <t-input-number :placeholder="lang.input" v-model="formData.grace_time"
                :min="0" theme="normal" :decimal-places="0" @blur="changeNum($event, 'grace_time')">
              </t-input-number>
            </t-form-item>
            <t-form-item name="grace_time_unit">
              <t-select v-model="formData.grace_time_unit" :placeholder="lang.select">
                <t-option v-for="item in timeArr" :value="item.value" :label="item.label" :key="item.value">
                </t-option>
              </t-select>
            </t-form-item>
            <t-tooltip :content="lang.product_set_text129" :show-arrow="false" theme="light"
              placement="top-left">
              <t-icon name="help-circle"></t-icon>
            </t-tooltip>
          </t-form-item>
          <t-form-item :label="lang.product_set_text128" name="keep_time">
            <t-form-item name="keep_time">
              <t-input-number :placeholder="lang.input" v-model="formData.keep_time"
                :min="0" theme="normal" :decimal-places="0" @blur="changeNum($event,'keep_time')">
              </t-input-number>
            </t-form-item>
            <t-form-item name="keep_time_unit">
              <t-select v-model="formData.keep_time_unit" :placeholder="lang.select">
                <t-option v-for="item in timeArr" :value="item.value" :label="item.label" :key="item.value">
                </t-option>
              </t-select>
            </t-form-item>
            <t-tooltip :content="lang.product_set_text130" :show-arrow="false" theme="light"
              placement="top-left">
              <t-icon name="help-circle"></t-icon>
            </t-tooltip>
          </t-form-item>
          <t-form-item label=" ">
            <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          </t-form-item>
          <div class="com-f-btn">
          
          </div>
        </t-form>
      </div>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/global_setting_demand.js"></script>
{include file="footer"}

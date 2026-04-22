{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/product.css">
<script src="/{$template_catalog}/template/{$themes}/js/common/jquery.min.js"></script>
<div id="content" class="product-api hasCrumb" v-cloak>
  <com-config>
    <!-- crumb -->
    <div class="com-crumb">
      <span>{{lang.refund_commodit_management}}</span>
      <t-icon name="chevron-right"></t-icon>
      <a href="product.htm">{{lang.product_list}}</a>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.interface_manage}}</span>
    </div>
    <t-card class="list-card-container com-sidebar-box">
      <div class="left-box">
        <ul class="common-tab">
          <li class="all com-first-title">{{lang.auth_all}}</li>
          <li v-permission="'auth_product_detail_basic_info_view'">
            <a :href="`product_detail.htm?id=${id}`">{{lang.basic_info}}</a>
          </li>
          <li class="active" v-permission="'auth_product_detail_server_view'">
            <a href="javascript:;">{{lang.interface_manage}}</a>
          </li>
          <li v-permission="'auth_product_detail_custom_field_view'">
            <a :href="`product_self_field.htm?id=${id}`">{{lang.product_selef_text1}}</a>
          </li>
          <li v-permission="'auth_product_detail_custom_host_name'">
            <a :href="`product_custom_name.htm?id=${id}`">{{lang.product_custom_name}}</a>
          </li>
        </ul>
      </div>
      <div class="box">
        <!-- <p class="com-tit"><span>{{ lang.interface_manage }}</span></p> -->
        <t-form :data="formData" ref="userInfo" @submit="onSubmit" label-align="top" :rules="rules">
          <div class="item">
            <t-form-item>
              <template slot="label">
                {{lang.auto_setup}}
                <t-tooltip :content="lang.auto_setup_tip" :show-arrow="false" theme="light" placement="top-left">
                  <t-icon name="help-circle" class="pack-tip"></t-icon>
                </t-tooltip>
              </template>
              <t-radio-group name="creating_notice_sms" v-model="formData.auto_setup" :options="checkOptions">
              </t-radio-group>
            </t-form-item>
            <t-form-item :label="lang.choose_interface_type" name="type">
              <t-select v-model="formData.type" @change="changeType" :disabled="isAgent">
                <t-option value="server" :label="lang.interface" key="server"></t-option>
                <t-option value="server_group" :label="`${lang.interface}${lang.group}`" key="server_group"></t-option>
              </t-select>
            </t-form-item>
            <t-form-item :label="lang.choose_interface" name="rel_id">
              <t-select v-model="formData.rel_id" :disabled="!formData.type || isAgent" filterable
                @change="chooseInterfaceId" :key="formData.rel_id">
                <t-option v-for="item in curList" :value="item.id" :label="item.name" :key="item.id">
                </t-option>
              </t-select>
            </t-form-item>
            <t-form-item>
              <template slot="label">
                {{lang.is_show_pro}}
                <t-tooltip :content="lang.show_pro_tip" :show-arrow="false" theme="light" placement="top-left">
                  <t-icon name="help-circle" class="pack-tip"></t-icon>
                </t-tooltip>
              </template>
              <t-select v-model="formData.show">
                <t-option :value="1" :label="lang.yes" key="1"></t-option>
                <t-option :value="0" :label="lang.login_no" key="0"></t-option>
              </t-select>
            </t-form-item>
            <t-form-item
              v-if="moduleType === 'mf_cloud' || moduleType === 'mf_dcim' ||  moduleType ===  'mf_dcim_cabinet'">
              <template slot="label">
                {{lang.is_show_base_info}}
                <t-tooltip :content="lang.is_show_base_info_tips" :show-arrow="false" theme="light"
                  placement="top-left">
                  <t-icon name="help-circle" class="pack-tip"></t-icon>
                </t-tooltip>
              </template>
              <t-select v-model="formData.show_base_info">
                <t-option :value="1" :label="lang.yes" key="1"></t-option>
                <t-option :value="0" :label="lang.login_no" key="0"></t-option>
              </t-select>
            </t-form-item>

            <t-form-item>
              <t-button theme="primary" type="submit" :loading="submitLoading"
                v-permission="'auth_product_detail_server_save_server'">{{lang.hold}}</t-button>
            </t-form-item>
          </div>
        </t-form>
        <!-- 后端渲染出来的配置页面 -->
        <div class="config-box" v-loading="loadingModule">
          <div class="content"></div>
        </div>
      </div>
    </t-card>
  </com-config>
</div>

<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/comTreeSelect/comTreeSelect.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/comRegionalMap/comRegionalMap.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/product_api.js"></script>
{include file="footer"}

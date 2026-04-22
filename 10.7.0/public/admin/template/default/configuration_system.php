{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-system" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li class="active" v-permission="'auth_system_configuration_system_configuration_system_configuration_view'">
          <a href="javascript:;">{{lang.system_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_debug'">
          <a href="configuration_debug.htm">{{lang.debug_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_access_configuration_view'">
          <a href="configuration_login.htm">{{lang.login_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_oss_management'">
          <a href="configuration_oss.htm">{{lang.oss_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_user_api_management'">
          <a href="configuration_api.htm">{{lang.user_api_text1}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_system_info_view'">
          <a style="display: flex; align-items: center;" href="configuration_upgrade.htm">{{lang.system_upgrade}}
            <img v-if="isCanUpdata" style="width: 20px; height: 20px; margin-left: 5px;"
              src="/{$template_catalog}/template/{$themes}/img/upgrade.svg">
          </a>
        </li>
        <li>
          <a href="configuration_cache.htm">{{lang.system_cache}}</a>
        </li>
      </ul>
      <div class="box">
        <p class="com-tit"><span>{{ lang.web_setting }}</span></p>
        <t-form :data="formData" :rules="rules" ref="formValidatorStatus" label-align="top" @submit="onSubmit">
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="website_name" :label="lang.site_name">
                <t-input v-model="formData.website_name" :placeholder="lang.site_name"></t-input>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="website_url" :label="lang.domain">
                <div slot="label" style="display: inline-flex; align-items: center;">
                  <span class="label">{{lang.domain}}</span>
                  <t-tooltip placement="top-right" :content="lang.domain_help" :show-arrow="false" theme="light">
                    <t-icon name="help-circle" size="18px" />
                  </t-tooltip>
                </div>
                <t-input v-model="formData.website_url" :placeholder="lang.domain"></t-input>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="clientarea_url" :label="lang.clientarea_url">
                <div slot="label" class="custom-label">
                  <span class="label">{{lang.clientarea_url}}</span>
                  <t-tooltip placement="top-right" :content="lang.clientarea_url_tip" :show-arrow="false" theme="light">
                    <t-icon name="help-circle" size="18px" />
                  </t-tooltip>
                </div>
                <t-input v-model="formData.clientarea_url" :placeholder="lang.clientarea_url_tip1"></t-input>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="www_url" :label="lang.www_url">
                <div slot="label" class="custom-label">
                  <span class="label">{{lang.www_url}}</span>
                  <t-tooltip placement="top-right" :content="lang.www_url_tip" :show-arrow="false" theme="light">
                    <t-icon name="help-circle" size="18px" />
                  </t-tooltip>
                </div>
                <t-input v-model="formData.www_url" :placeholder="lang.www_url_tip1"></t-input>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.back_language">
                <t-select v-model="formData.lang_admin">
                  <t-option v-for="item in adminArr" :value="item.display_lang" :label="item.display_name"
                    :key="item.display_lang"></t-option>
                </t-select>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.font_language">
                <t-select v-model="formData.lang_home">
                  <t-option v-for="item in homeArr" :value="item.display_lang" :label="item.display_name"
                    :key="item.display_lang"></t-option>
                </t-select>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="terms_service_url" :label="lang.service_address">
                <t-input v-model="formData.terms_service_url" :placeholder="lang.service_address"></t-input>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="terms_privacy_url" :label="lang.privacy_clause_address">
                <t-input v-model="formData.terms_privacy_url" :placeholder="lang.privacy_clause_address"></t-input>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="clientarea_logo_url" :label="lang.logo_url">
                <t-input v-model="formData.clientarea_logo_url" :placeholder="lang.logo_url"></t-input>
              </t-form-item>
            </t-col>
          </t-row>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="system_logo" :label="lang.member_center + 'LOGO'" :help="`${lang.size}：${lang.width}130px，${lang.height}28px；${lang.logo_size}：≤2M`">
                <t-upload ref="uploadRef3" :size-limit="{ size: 2, unit: 'MB' }" :action="uploadUrl"
                  v-model="formData.system_logo" :auto-upload="true" @fail="handleFail" theme="custom"
                  :headers="uploadHeaders" accept="image/*" :format-response="formatImgResponse">
                  <div class="upload">
                    <t-icon name="add" size="24px"></t-icon>
                    <span class="txt">{{lang.upload_img}}</span>
                  </div>
                </t-upload>
                <div class="logo" v-if="formData.system_logo[0]?.url">
                  <div class="box">
                    <img :src="formData.system_logo[0]?.url" alt="">
                    <div class="hover" @click="deleteLogo" v-if="formData.system_logo[0]?.url">
                      <t-icon name="delete"></t-icon>
                    </div>
                  </div>
                  <!-- <span class="name">{{formData.system_logo[0]?.url.split('^')[1]}}</span> -->
                </div>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="tab_logo" :label="lang.label_page + 'LOGO'" :help="`${lang.size}：${lang.width}32px，${lang.height}32px；${lang.logo_size}：≤2M`">
                <t-upload ref="uploadRef3" :size-limit="{ size: 2, unit: 'MB' }" :action="uploadUrl"
                  v-model="formData.tab_logo" :auto-upload="true" @fail="handleFail" theme="custom"
                  :headers="uploadHeaders" accept="image/*" :format-response="formatImgResponse">
                  <div class="upload">
                    <t-icon name="add" size="24px"></t-icon>
                    <span class="txt">{{lang.upload_img}}</span>
                  </div>
                </t-upload>
                <div class="logo tab" v-if="formData.tab_logo[0]?.url">
                  <div class="box">
                    <img :src="formData.tab_logo[0]?.url" alt="">
                    <div class="hover" @click="deleteTabLogo" v-if="formData.tab_logo[0]?.url">
                      <t-icon name="delete"></t-icon>
                    </div>
                  </div>
                  <!-- <span class="name">{{formData.tab_logo[0]?.url.split('^')[1]}}</span> -->
                </div>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6" v-if="formData.edition == 1">
              <t-form-item name="admin_logo" :label="lang.theme_text1 + 'LOGO'" :help="`${lang.size}：${lang.width}130px，${lang.height}28px；${lang.logo_size}：≤2M`">
                <t-upload ref="uploadRef3" :size-limit="{ size: 2, unit: 'MB' }" :action="uploadUrl"
                  v-model="formData.admin_logo" :auto-upload="true" @fail="handleFail" theme="custom"
                  :headers="uploadHeaders" accept="image/*" :format-response="formatImgResponse">
                  <div class="upload">
                    <t-icon name="add" size="24px"></t-icon>
                    <span class="txt">{{lang.upload_img}}</span>
                  </div>
                </t-upload>
                <div class="logo" v-if="formData.admin_logo[0]?.url">
                  <div class="box">
                    <img :src="formData.admin_logo[0]?.url" alt="">
                    <div class="hover" @click="deleteAdminLogo" v-if="formData.admin_logo[0]?.url">
                      <t-icon name="delete"></t-icon>
                    </div>
                  </div>
                  <!-- <span class="name">{{formData.system_logo[0]?.url.split('^')[1]}}</span> -->
                </div>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.logo_blank">
                <t-radio-group name="clientarea_logo_url_blank" v-model="formData.clientarea_logo_url_blank">
                  <t-radio value="1">{{lang.blank_page}}</t-radio>
                  <t-radio value="0">{{lang.parent_page}}</t-radio>
                </t-radio-group>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6" class="service">
              <div class="maintain">
                <t-form-item name="lang_admin" :label="lang.maintenance_mode">
                  <t-radio-group name="maintenance_mode" v-model="formData.maintenance_mode">
                    <t-radio value="1">{{lang.open}}</t-radio>
                    <t-radio value="0">{{lang.close}}</t-radio>
                  </t-radio-group>
                </t-form-item>
                <t-form-item v-if="formData.maintenance_mode == '1'" :label="lang.maintenance_mode_info"
                  name="maintenance_mode_message">
                  <t-textarea :placeholder="lang.maintenance_mode_info" v-model="formData.maintenance_mode_message" />
                </t-form-item>
              </div>
            </t-col>
          </t-row>
          <!-- 基础设置 -->
          <p class="com-tit"><span>{{ lang.basic_setting }}</span></p>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="client_start_id_value" :label="lang.client_start_id">
                <t-input-number v-model="formData.client_start_id_value" :placeholder="lang.client_start_id"
                  theme="normal" :max="99999999" :min="1" :decimal-places="0"></t-input-number>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="order_start_id_value" :label="lang.order_start_id">
                <t-input-number v-model="formData.order_start_id_value" :placeholder="lang.order_start_id"
                  theme="normal" :max="99999999" :min="1" :decimal-places="0"></t-input-number>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6" v-if="client_custom_field_list.length !== 0">
              <t-form-item name="client_custom_field" :label="lang.client_custom_label35">
                <div slot="label" class="custom-label">
                  <span class="label">{{lang.client_custom_label35}}</span>
                  <t-tooltip placement="top-right" :content="lang.client_custom_label36" :show-arrow="false"
                    theme="light">
                    <t-icon name="help-circle" size="18px" />
                  </t-tooltip>
                </div>
                <t-select v-model="formData.customfield.client_custom_field.id" clearable>
                  <t-option v-for="item in client_custom_field_list" :value="item.id" :label="item.name" :key="item.id">
                  </t-option>
                </t-select>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.account_change">
                <div slot="label" class="custom-label">
                  <span class="label">{{lang.account_change}}</span>
                  <t-tooltip placement="top-right" :content="lang.client_custom_label37" :show-arrow="false"
                    theme="light">
                    <t-icon name="help-circle" size="18px" />
                  </t-tooltip>
                </div>
                <t-select v-model="formData.prohibit_user_information_changes" :min-collapsed-num="2" multiple
                  clearable>
                  <t-option v-for="item in user_information_fields" :value="item.id" :label="item.name" :key="item.id">
                  </t-option>
                </t-select>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="ip_white_list">
                <div slot="label" class="custom-label">
                  <span class="label">{{lang.ip_white_list}}</span>
                  <t-tooltip placement="top-right" :show-arrow="false" theme="light">
                    <template #content>
                      <div>{{lang.ip_white_list_help1}}</div>
                      <div>{{lang.ip_white_list_help2}}</div>
                      <div>{{lang.ip_white_list_help3}}</div>
                    </template>
                    <t-icon name="help-circle" size="18px" />
                  </t-tooltip>
                </div>
                <t-textarea :placeholder="lang.ip_white_list_tip" v-model="formData.ip_white_list"></t-textarea>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="global_list_limit" :label="lang.global_list_num">
                <t-input-number v-model="formData.global_list_limit" :placeholder="lang.global_default_num"
                  theme="normal" :max="500" :min="1" :decimal-places="0">
                  <template #suffix><span>{{lang.global_per_page}}</span></template>
                </t-input-number>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.isAllowChooseLan">
                <t-radio-group name="creating_notice_sms" v-model="formData.lang_home_open">
                  <t-radio value="1">{{lang.allow}}</t-radio>
                  <t-radio value="0">{{lang.prohibit}}</t-radio>
                </t-radio-group>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.show_del_order">
                <t-radio-group name="home_show_deleted_host" v-model="formData.home_show_deleted_host">
                  <t-radio value="1">{{lang.show_del_yes}}</t-radio>
                  <t-radio value="0">{{lang.show_del_no}}</t-radio>
                </t-radio-group>
              </t-form-item>
            </t-col>
          </t-row>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
            <t-col :xs="12" :xl="12" :md="12">
              <t-form-item :label="lang.dont_save_password">
                <t-switch size="large" :custom-value="[1,0]" v-model="formData.donot_save_client_product_password"></t-switch>
                <span>{{lang.dont_save_password_tip}}</span>
              </t-form-item>
            </t-col>
          </t-row>
          <t-form-item class="com-is-fixed broaden" :class="{'has-shadow': !footerInView}">
            <t-button theme="primary" type="submit" :loading="submitLoading"
              v-permission="'auth_system_configuration_system_configuration_system_configuration_save_configuration'">
              {{lang.hold}}
            </t-button>
          </t-form-item>
        </t-form>
      </div>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_system.js"></script>
{include file="footer"}

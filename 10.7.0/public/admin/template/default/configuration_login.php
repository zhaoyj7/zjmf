{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-system configuration-login" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li v-permission="'auth_system_configuration_system_configuration_system_configuration_view'">
          <a href="configuration_system.htm">{{lang.system_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_system_configuration_debug'">
          <a href="configuration_debug.htm">{{lang.debug_setting}}</a>
        </li>
        <li class="active" v-permission="'auth_system_configuration_system_configuration_access_configuration_view'">
          <a href="javascript:;">{{lang.login_setting}}</a>
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
        <t-form :data="formData" :required-mark="false" :label-width="80" label-align="top" ref="formValidatorStatus"
          :rules="rules" @submit="onSubmit">
          <t-tabs v-model="tabValue" placement="left">
            <t-tab-panel value="client" :label="lang.setting_text1">
              <div class="tab-content">
                <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item :label="lang.setting_text3">
                      <t-radio-group name="register_phone" v-model="formData.register_phone">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item :label="lang.setting_text4">
                      <t-radio-group name="code_client_phone_register" v-model="formData.code_client_phone_register">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item :label="lang.setting_text142">
                      <t-radio-group name="login_phone_password" v-model="formData.login_phone_password">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item :label="lang.setting_text8">
                      <t-radio-group name="login_phone_verify" v-model="formData.login_phone_verify">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                </t-row>
                <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item :label="lang.setting_text5">
                      <t-radio-group v-model="formData.register_email">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item :label="lang.setting_text6">
                      <t-radio-group name="code_client_email_register" v-model="formData.code_client_email_register">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item :label="lang.setting_text93">
                      <t-radio-group v-model="formData.login_email_password">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item>
                      <div slot="label" class="custom-label">
                        <span class="label">{{lang.support_email_type}}</span>
                        <t-tooltip placement="top-right" :content="lang.support_email_tip" :show-arrow="false"
                          theme="light">
                          <t-icon name="help-circle" size="18px" />
                        </t-tooltip>
                      </div>
                      <t-radio-group name="limit_email_suffix" v-model="formData.limit_email_suffix">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                      <t-input style="width: 200px;" v-if="formData.limit_email_suffix == '1'"
                        v-model="formData.email_suffix" :placeholder="lang.support_email_placeholder"></t-input>
                    </t-form-item>
                  </t-col>
                </t-row>
                <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="home_login_check_ip">
                      <div slot="label" class="custom-label">
                        <span class="label">{{lang.setting_text17}}</span>
                        <t-tooltip placement="top-right" :content="lang.setting_text18" :show-arrow="false"
                          theme="light">
                          <t-icon name="help-circle" size="18px" />
                        </t-tooltip>
                      </div>
                      <t-radio-group name="home_login_check_ip" v-model="formData.home_login_check_ip">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="home_login_check_common_ip" :label="lang.setting_text7">
                      <t-radio-group name="home_login_check_common_ip" v-model="formData.home_login_check_common_ip">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <!-- 登录注册页面展示跳转按钮 -->
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="login_register_redirect_show" :label="lang.setting_text148">
                      <t-radio-group name="login_register_redirect_show"
                        v-model="formData.login_register_redirect_show">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <template v-if="formData.login_register_redirect_show">
                    <t-col :xs="12" :xl="3" :md="6">
                      <t-form-item name="login_register_redirect_blank" :label="lang.setting_text151">
                        <t-radio-group name="login_register_redirect_blank"
                          v-model="formData.login_register_redirect_blank">
                          <t-radio :value="1">{{lang.yes}}</t-radio>
                          <t-radio :value="0">{{lang.login_no}}</t-radio>
                        </t-radio-group>
                      </t-form-item>
                    </t-col>
                    <t-col :xs="12" :xl="3" :md="6">
                      <t-form-item name="login_register_redirect_text" :label="lang.setting_text149">
                        <t-input v-model="formData.login_register_redirect_text" :placeholder="lang.setting_text149">
                        </t-input>
                      </t-form-item>
                    </t-col>
                    <t-col :xs="12" :xl="3" :md="6">
                      <t-form-item name="login_register_redirect_url" :label="lang.setting_text150">
                        <t-input v-model="formData.login_register_redirect_url" :placeholder="lang.setting_text150">
                        </t-input>
                      </t-form-item>
                    </t-col>

                  </template>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="home_enforce_safe_method">
                      <div slot="label" class="custom-label">
                        <span class="label">{{lang.setting_text19}}</span>
                        <t-tooltip placement="top-right" :content="lang.setting_text20" :show-arrow="false"
                          theme="light">
                          <t-icon name="help-circle" size="18px" />
                        </t-tooltip>
                      </div>
                      <t-select class="short-select" v-model="formData.home_enforce_safe_method" :min-collapsed-num="2"
                        multiple clearable>
                        <t-option v-for="item in homeSafeMethodList" :value="item.value" :label="item.label"
                          :key="item.value">
                        </t-option>
                      </t-select>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="home_login_ip_exception_verify">
                      <div slot="label" class="custom-label">
                        <span class="label">{{lang.safe_method}}</span>
                        <t-tooltip placement="top-right" :content="lang.safe_method_tip" :show-arrow="false"
                          theme="light">
                          <t-icon name="help-circle" size="18px" />
                        </t-tooltip>
                      </div>
                      <t-select style="width: 100%;" v-model="formData.home_login_ip_exception_verify"
                        :min-collapsed-num="2" multiple clearable>
                        <t-option v-for="item in homeSafeMethodType" :value="item.value" :label="item.label"
                          :key="item.value">
                        </t-option>
                      </t-select>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="exception_login_certification_plugin">
                      <div slot="label" class="custom-label">
                        <span class="label">{{lang.exception_login_certification_plugin}}</span>
                        <t-tooltip placement="top-right" :content="lang.exception_login_certification_plugin_tip"
                          :show-arrow="false" theme="light">
                          <t-icon name="help-circle" size="18px" />
                        </t-tooltip>
                      </div>
                      <t-select style="width: 100%;" v-model="formData.exception_login_certification_plugin" clearable>
                        <t-option v-for="item in exception_login_certification_plugin_list" :value="item.name"
                          :label="item.title" :key="item.name">
                        </t-option>
                      </t-select>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="first_login_method" :label="lang.setting_text143">
                      <t-select style="width: 100%;" v-model="formData.first_login_method">
                        <t-option value="code" :label="lang.setting_text88"></t-option>
                        <t-option value="password" :label="lang.setting_text89"></t-option>
                      </t-select>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="first_password_login_method" :label="lang.setting_text90">
                      <t-select style="width: 100%;" v-model="formData.first_password_login_method">
                        <t-option value="email" :label="lang.setting_text91"></t-option>
                        <t-option value="phone" :label="lang.setting_text92"></t-option>
                      </t-select>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="first_login_type" :label="lang.setting_text87">
                      <t-select style="width: 100%;" v-model="formData.first_login_type">
                        <t-option v-for="item in formData.first_login_type_list" :value="item.value" :label="item.name"
                          :key="item.value">
                        </t-option>
                      </t-select>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="home_login_expire_time" :label="lang.setting_text105">
                      <t-input-number v-model="formData.home_login_expire_time" :suffix="lang.debug_minutes"
                        :placeholder="lang.setting_text105" theme="normal" :min="0" :decimal-places="0">
                      </t-input-number>
                    </t-form-item>
                  </t-col>
                </t-row>
              </div>
              <t-form-item class="com-is-fixed broaden" :class="{'has-shadow': !footerInView}">
                <t-button theme="primary" type="submit" :loading="submitLoading"
                  v-permission="'auth_system_configuration_system_configuration_access_configuration_save_configuration'">{{lang.hold}}</t-button>
              </t-form-item>
            </t-tab-panel>
            <t-tab-panel value="admin" :label="lang.setting_text2">
              <div class="tab-content">
                <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="admin_login_check_ip">
                      <div slot="label" class="custom-label">
                        <span class="label">{{lang.setting_text21}}</span>
                        <t-tooltip placement="top-right" :content="lang.setting_text18" :show-arrow="false"
                          theme="light">
                          <t-icon name="help-circle" size="18px" />
                        </t-tooltip>
                      </div>
                      <t-radio-group name="admin_login_check_ip" v-model="formData.admin_login_check_ip">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="admin_allow_remember_account" :label="lang.setting_text144">
                      <t-radio-group name="admin_allow_remember_account"
                        v-model="formData.admin_allow_remember_account">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="admin_login_password_encrypt" :label="lang.setting_text152">
                      <t-radio-group name="admin_login_password_encrypt"
                        v-model="formData.admin_login_password_encrypt">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="admin_enforce_safe_method">
                      <div slot="label" class="custom-label">
                        <span class="label">{{lang.setting_text19}}</span>
                        <t-tooltip placement="top-right" :content="lang.setting_text23" :show-arrow="false"
                          theme="light">
                          <t-icon name="help-circle" size="18px" />
                        </t-tooltip>
                      </div>
                      <t-select class="short-select" v-model="formData.admin_enforce_safe_method" :min-collapsed-num="1"
                        multiple clearable @change="changeAdmin">
                        <t-option v-for="item in adminMethodList" :value="item.value" :label="item.label"
                          :key="item.value">
                        </t-option>
                      </t-select>
                      <!-- 后台启用场景 -->
                      <t-select class="short-select" v-model="formData.admin_enforce_safe_method_scene"
                        :min-collapsed-num="1" multiple clearable v-show="formData.admin_enforce_safe_method.length > 0"
                        @change="changeScene" :placeholder="lang.setting_text73">
                        <t-option v-for="item in adminScene" :value="item.value" :label="item.label" :key="item.value">
                        </t-option>
                      </t-select>
                    </t-form-item>
                  </t-col>
                </t-row>

                <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="admin_second_verify" :label="lang.setting_text94">
                      <t-radio-group name="admin_second_verify" v-model="formData.admin_second_verify">
                        <t-radio :value="1">{{lang.upstream_text126}}</t-radio>
                        <t-radio :value="0">{{lang.upstream_text127}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6" v-if="formData.admin_second_verify === 1">
                    <t-form-item name="admin_second_verify_method_default">
                      <div slot="label" class="custom-label">
                        <span class="label">{{lang.setting_text95}}</span>
                        <t-tooltip placement="top-right" :content="lang.setting_text96" :show-arrow="false"
                          theme="light">
                          <t-icon name="help-circle" size="18px" />
                        </t-tooltip>
                      </div>
                      <t-select style="width: 100%;" v-model="formData.admin_second_verify_method_default">
                        <t-option value="totp" label="TOTP"></t-option>
                        <t-option value="sms" :label="lang.setting_text97"></t-option>
                        <t-option value="email" :label="lang.setting_text98"></t-option>
                      </t-select>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="prohibit_admin_bind_phone" :label="lang.setting_text99">
                      <t-radio-group v-model="formData.prohibit_admin_bind_phone">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="prohibit_admin_bind_email" :label="lang.setting_text100">
                      <t-radio-group v-model="formData.prohibit_admin_bind_email">
                        <t-radio :value="1">{{lang.yes}}</t-radio>
                        <t-radio :value="0">{{lang.login_no}}</t-radio>
                      </t-radio-group>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="admin_password_or_verify_code_retry_times" :label="lang.setting_text101">
                      <t-input-number v-model="formData.admin_password_or_verify_code_retry_times"
                        :placeholder="lang.setting_text102" theme="normal" :min="0" :decimal-places="0">
                      </t-input-number>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="admin_frozen_time" :label="lang.setting_text103">
                      <t-input-number v-model="formData.admin_frozen_time" :suffix="lang.debug_minutes"
                        :placeholder="lang.setting_text104" theme="normal" :min="0" :decimal-places="0">
                      </t-input-number>
                    </t-form-item>
                  </t-col>
                  <t-col :xs="12" :xl="3" :md="6">
                    <t-form-item name="admin_login_expire_time" :label="lang.setting_text105">
                      <t-input-number v-model="formData.admin_login_expire_time" :suffix="lang.debug_minutes"
                        :placeholder="lang.setting_text105" theme="normal" :min="0" :decimal-places="0">
                      </t-input-number>
                    </t-form-item>
                  </t-col>
                </t-row>

                <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
                  <t-col :xs="24" :xl="12" :md="12">
                    <t-form-item name="admin_login_ip_whitelist">
                      <div slot="label" class="custom-label">
                        <span class="label">{{lang.admin_login_ip_whitelist_label}}</span>
                        <t-tooltip placement="top-right" :content="lang.admin_login_ip_whitelist_tip"
                          :show-arrow="false" theme="light">
                          <t-icon name="help-circle" size="18px" />
                        </t-tooltip>
                      </div>
                      <t-textarea v-model="formData.admin_login_ip_whitelist"
                        :placeholder="lang.admin_login_ip_whitelist_placeholder" :rows="5" :maxlength="2000">
                      </t-textarea>
                    </t-form-item>
                  </t-col>
                </t-row>
              </div>
              <t-form-item class="com-is-fixed broaden" :class="{'has-shadow': !footerInView}">
                <t-button theme="primary" type="submit" :loading="submitLoading"
                  v-permission="'auth_system_configuration_system_configuration_access_configuration_save_configuration'">{{lang.hold}}</t-button>
              </t-form-item>
            </t-tab-panel>
          </t-tabs>
        </t-form>
      </div>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_login.js"></script>
{include file="footer"}

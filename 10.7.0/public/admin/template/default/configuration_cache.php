{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-system configuration-cache" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li v-permission="'auth_system_configuration_system_configuration_system_configuration_view'">
          <a href="configuration_system.htm">{{lang.system_setting}}</a>
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
        <li class="active">
          <a href="javascript:;">{{lang.system_cache}}</a>
        </li>
      </ul>
      <div class="box">
        <h3 clsas="com-tit">{{lang.system_cache_statistics}}</h3>
        <div class="cache-list" v-loading="loading">
          <p class="item" v-for="(item, index) in cacheList" :key="index">{{item.value}}： {{item.label}}</p>
        </div>
        <div class="clear-btn">
          <t-button @click="handleClar('all')" :loading="allLoading">{{lang.clear_all_cache}}</t-button>
          <t-button @click="handleClar('plugin')" :loading="pluginLoading">{{lang.clear_plugin_cache}}</t-button>
          <t-button @click="handleClar('config')" :loading="configLoading">{{lang.clear_config_cache}}</t-button> 
          <t-button @click="handleClar('route')" :loading="langLoading">{{lang.clear_route_cache}}</t-button>
          <t-button @click="handleClar('lang')" :loading="langLoading">{{lang.clear_language_cache}}</t-button>
        </div>
      </div>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_cache.js"></script>
{include file="footer"}

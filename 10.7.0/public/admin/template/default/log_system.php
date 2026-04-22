{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/manage.css">
<div id="content" class="log-system" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li class="active" v-permission="'auth_management_log_system_log'">
          <a href="javascript:;">{{lang.system_log}}</a>
        </li>
        <li v-permission="'auth_management_log_notice_log'">
          <a href="log_notice_sms.htm">{{lang.notice_log}}</a>
        </li>
      </ul>
      <div class="log-box flex">
        <t-tabs v-model="params.type" @change="changeTab" placement="left" style="flex-shrink: 0; margin-right: 20px">
          <t-tab-panel :value="item.value" :label="item.name" v-for="item in logType"
            :key="item.value">
          </t-tab-panel>
        </t-tabs>
        <div class="right-box">
          <div class="export-header">
            <div class="left flex">
              <t-button theme="primary" @click="openExportDia"
                v-if="$checkPermission('auth_management_log_system_log_export_excel') && hasExport">
                {{lang.data_export}}
              </t-button>
            </div>
            <div class="right-search flex">
              <t-date-range-picker allow-input enable-time-picker clearable v-model="searchRange" @change="search"
                style="width: 360px;">
              </t-date-range-picker>
              <t-input style="width: 200px;" class="com-gap" v-model="params.admin_name"
                :placeholder="`${lang.data_export_tip15}`" @keypress.enter.native="search" :on-clear="search" clearable>
              </t-input>
              <t-input style="width: 200px;" class="com-gap" v-model="params.keywords"
                :placeholder="`${lang.data_export_tip14}`" @keypress.enter.native="search" :on-clear="search" clearable>
              </t-input>
              <t-button @click="search" class="com-gap">{{lang.query}}</t-button>
            </div>
          </div>
          <t-table row-key="id" :data="data" size="medium" :hide-sort-tips="true" :columns="columns" :hover="hover"
            :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange">
            <template slot="sortIcon">
              <t-icon name="caret-down-small"></t-icon>
            </template>
            <template #description="{row}">
              <span v-html="calStr(row.description)"></span>
            </template>
            <template #create_time="{row}">
              {{moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}
            </template>
          </t-table>
          <com-pagination v-if="total" :total="total"
            :total-content="false" :show-page-number="false"
            :show-first-and-last-page-btn="false"
            :show-previous-and-next-btn="false"
            :cur-page-length="data.length"
            :show-custom-buttons="true"
            :page="params.page" :limit="params.limit" @page-change="changePage">
          </com-pagination>
        </div>
      </div>
    </t-card>

    <t-dialog :header="lang.data_export" :visible.sync="exportVisible" :footer="false">
      <div style="margin-bottom: 20px;">
        <t-date-range-picker allow-input clearable v-model="range" style="width: 100%;">
        </t-date-range-picker>
        <p style="margin-top: 5px; color: var(--td-text-color-placeholder);">
          <span style="margin-right: var(--td-comp-margin-xs);
            color: var(--td-error-color);
            line-height: var(--td-line-height-body-medium);">*</span>
          {{lang.export_range_tips}}
        </p>
      </div>
      <div class="com-f-btn">
        <t-button theme="primary" type="submit" :loading="exportLoading"
          @click="handelDownload">{{lang.sure}}</t-button>
        <t-button theme="default" variant="base" @click="exportVisible = false">{{lang.cancel}}</t-button>
      </div>
    </t-dialog>



  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/manage.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/log_system.js"></script>
{include file="footer"}

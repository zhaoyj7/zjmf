{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/manage.css">
<div id="content" class="log-notice-email" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li v-permission="'auth_management_log_system_log'">
          <a href="log_system.htm">{{lang.system_log}}</a>
        </li>
        <li class="active" v-permission="'auth_management_log_notice_log'">
          <a href="javascript:;">{{lang.notice_log}}</a>
        </li>
      </ul>
      <div class="common-header">
        <div class="left">
          <t-select value="email" class="choose-select-btn" @change="jump">
            <t-option key="sms" :label="lang.sms_notice" value="sms"></t-option>
            <t-option key="email" :label="lang.email_notice" value="email"></t-option>
          </t-select>
          <!-- <t-button theme="default" class="com-gray-btn" @click="jump">{{lang.sms_notice}}</t-button>
          <t-button>{{lang.email_notice}}</t-button> -->
          <t-button theme="default" class="com-gray-btn" @click="openExportDia"
            v-if="$checkPermission('auth_management_log_email_log_export_excel') && hasExport">
            {{lang.data_export}}
          </t-button>
        </div>
        <div class="flex">
          <t-input v-model="params.keywords" class="search-input" :placeholder="`${lang.client_notice_email_search}`"
            @keypress.enter.native="search" :on-clear="clearKey" clearable>
          </t-input>
          <t-button @click="search">{{lang.query}}</t-button>
        </div>
      </div>
      <t-table row-key="id" :data="data" size="medium" :hide-sort-tips="true" :columns="columns" :hover="hover"
        :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #subject="{row}">
          <t-icon v-if="row.status === 1" name="check-circle-filled" style="color:#00a870;"></t-icon>
          <template v-else>
            <t-tooltip :content="row.fail_reason" theme="light" :show-arrow="false">
              <t-icon name="close-circle-filled" class="icon-error" style="color: #e34d59;"></t-icon>
            </t-tooltip>
          </template>
          <a class="aHover" @click="showMessage(row)">{{row.subject}}</a>
        </template>
        <template #create_time="{row}">
          {{moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}
        </template>
        <template #phone="{row}">
          +{{row.phone_code}}&nbsp;-&nbsp;{{row.phone}}
        </template>
      </t-table>
      <com-pagination v-if="total" :total="total"
        :page="params.page" :limit="params.limit"
        @page-change="changePage">
      </com-pagination>
    </t-card>
    <t-dialog :visible.sync="messageVisable" width="80%" :footer="false" class="messagePop" :close-btn="false">
      <div slot="header" class="messageHeader">
        <span>{{emailTitle}}</span>
        <t-icon name="close" @click="messageVisable=false"></t-icon>
      </div>
      <div v-html="messagePop"></div>
    </t-dialog>

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
<script src="/{$template_catalog}/template/{$themes}/js/log_notice_email.js"></script>
{include file="footer"}

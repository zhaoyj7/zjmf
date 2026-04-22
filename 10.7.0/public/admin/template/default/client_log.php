{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="log client-log hasCrumb" v-cloak>
  <com-config>
    <div class="com-crumb">
      <span>{{lang.user_manage}}</span>
      <t-icon name="chevron-right"></t-icon>
      <a href="client.htm" v-permission="'auth_user_list_view'">{{lang.user_list}}</a>
      <t-icon name="chevron-right" v-permission="'auth_user_list_view'"></t-icon>
      <span class="cur">{{lang.operation}}{{lang.log}}</span>
    </div>
    <t-card class="list-card-container">
      <div class="com-h-box">
        <ul class="common-tab">
          <li v-permission="'auth_user_detail_personal_information_view'">
            <a :href="`${baseUrl}/client_detail.htm?id=${id}`">{{lang.personal}}</a>
          </li>
          <li v-permission="'auth_user_detail_host_info_view'">
            <a :href="`${baseUrl}/client_host.htm?id=${id}`">{{lang.product_info}}</a>
          </li>
          <li v-permission="'auth_user_detail_order_view'">
            <a :href="`${baseUrl}/client_order.htm?id=${id}`">{{lang.order_manage}}</a>
          </li>
          <li v-permission="'auth_user_detail_transaction_view'">
            <a :href="`${baseUrl}/client_transaction.htm?id=${id}`">{{lang.flow}}</a>
          </li>
          <li class="active" v-permission="'auth_user_detail_operation_log'">
            <a href="javascript:;">{{lang.operation}}{{lang.log}}</a>
          </li>
          <li
            v-if="$checkPermission('auth_user_detail_notification_log_sms_notification') || $checkPermission('auth_user_detail_notification_log_email_notification')">
            <a
              :href="`${baseUrl}/${($checkPermission('auth_user_detail_notification_log_sms_notification') ? 'client_notice_sms' : 'client_notice_email')}.htm?id=${id}`">{{lang.notice_log}}</a>
          </li>
          <li v-if="hasNewTicket && $checkPermission('auth_user_detail_ticket_premium_view')">
            <a :href="`${baseUrl}/plugin/ticket_premium/client_ticket.htm?id=${id}`">{{lang.auto_order}}</a>
          </li>
          <li v-if="!hasNewTicket && hasTicket && $checkPermission('auth_user_detail_ticket_view')">
            <a :href="`${baseUrl}/plugin/idcsmart_ticket/client_ticket.htm?id=${id}`">{{lang.auto_order}}</a>
          </li>
          <li v-if="hasRecommend ">
            <a
              :href="`${baseUrl}/plugin/idcsmart_recommend/client_recommend.htm?id=${id}`">{{lang.data_export_tip9}}</a>
          </li>
          <li v-permission="'auth_user_detail_info_record_view'">
            <a :href="`${baseUrl}/client_records.htm?id=${id}`">{{lang.info_records}}</a>
          </li>
        </ul>
        <!-- 顶部右侧选择用户 -->
        <com-choose-user :cur-info="clientDetail" :clearable="false" @changeuser="changeUser" class="com-clinet-choose">
        </com-choose-user>
      </div>
      <div class="common-header">
        <div class="left">

        </div>
        <div class="right-search flex">

          <t-date-range-picker allow-input enable-time-picker clearable v-model="searchRange" @change="search"
            style="width: 360px;">
          </t-date-range-picker>
          <t-input style="width: 200px;" v-model="params.admin_name" :placeholder="`${lang.data_export_tip15}`"
            @keypress.enter.native="search" :on-clear="search" clearable>
          </t-input>
          <t-input style="width: 200px;" v-model="params.keywords" :placeholder="`${lang.client_log_search}`"
            @keypress.enter.native="search" :on-clear="clearKey" clearable>
            <template #suffix-icon>
              <t-icon size="20px" name="search" @click="search"></t-icon>
            </template>
          </t-input>
          <t-button @click="search">{{lang.query}}</t-button>
        </div>
      </div>
      <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading"
        :table-layout="tableLayout ? 'auto' : 'fixed'" :hide-sort-tips="true" @sort-change="sortChange">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #description="{row}">
          <span v-html="row.description"></span>
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
        :page="params.page"
        :limit="params.limit"
        @page-change="changePage">
      </com-pagination>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/components/comChooseUser/comChooseUser.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/client_log.js"></script>
{include file="footer"}

{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="client" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="common-header">
        <div class="flex">
          <t-button @click="addUser" class="add" v-if="$checkPermission('auth_user_list_create_user')">
            {{lang.create_user}}
          </t-button>
          <t-button theme="default" @click="exportVisible = true" class="down-export com-gray-btn"
            v-if="$checkPermission('auth_user_list_export_excel') && hasExport">
            {{lang.data_export}}
          </t-button>
          <t-checkbox v-model="params.show_sub_client" @change="getClientList">
            <span class="second-text-color">{{lang.user_text23}}</span>
          </t-checkbox>
        </div>
        <div class="client-search">
          <!-- 选择销售 -->
          <t-select v-model="curSaleId" :placeholder="lang.please_choose_sale" clearable v-if="hasSale">
            <t-option v-for="item in allSales" :value="item.id" :label="item.name" :key="item.name">
            </t-option>
          </t-select>
          <t-select v-model="curLevelId" :placeholder="lang.clinet_level" clearable v-if="hasPlugin">
            <t-option v-for="item in levelList" :value="item.id" :label="item.name" :key="item.name">
            </t-option>
          </t-select>
          <t-select v-model="params.type" class="client-type" filterable>
            <t-option v-for="item in typeOption" :value="item.value" :label="item.label" :key="item.value"></t-option>
          </t-select>
          <t-input v-model="params.keywords" @keypress.enter.native="search" :placeholder="lang.input"
            :on-clear="clearKey" clearable>
          </t-input>
          <t-button @click="search" class="ml8">{{lang.query}}</t-button>
          <com-view-filed view="client" @changefield="changeField"></com-view-filed>
        </div>
      </div>
      <t-table row-key="id" :data="calcList" size="medium" :columns="columns" :hover="hover" :loading="loading"
        :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" display-type="fixed-width"
        :hide-sort-tips="true" resizable>
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #id="{row}">
          <a :href="`client_detail.htm?client_id=${row.id}`" class="aHover" v-if="showDetails">{{row.id}}</a>
          <span v-else>{{row.id}}</span>
        </template>
        <template #certification="{row}">
          <t-tooltip :show-arrow="false" theme="light">
            <span slot="content">{{!row.certification ? lang.real_tip8 : row.certification_type === 'person' ?
                      lang.real_tip9 : lang.real_tip10}}</span>
            <t-icon :class="row.certification ? 'green-icon' : ''"
              :name="!row.certification ? 'user-clear': row.certification_type === 'person' ? 'user' : 'usergroup'" />
          </t-tooltip>
        </template>
        <template #e-mail="{row}">
          <span>{{row.email || '--'}}</span>
        </template>
        <template #sale="{row}">
          <span>{{row.sale || '--'}}</span>
        </template>
        <template #oauth="{row}">
          <div style="display: flex; flex-wrap: wrap; align-items: center; row-gap: 5px; column-gap: 5px;">
            <template v-for="item in row.oauth">
              <img style="width: 25px; height: 25px;" :src="item" alt="">
            </template>
          </div>
        </template>
        <template #mp_weixin_notice="{row}">
          <t-tooltip :show-arrow="false" theme="light">
            <div slot="content">{{row.mp_weixin_notice == 1  ? lang.product_set_text102 : lang.product_set_text103}}
            </div>
            <img style="width: 25px; height: 25px;"
              :src="row.mp_weixin_notice == 1 ? '/{$template_catalog}/template/{$themes}/img/weixin_notice.svg' : '/{$template_catalog}/template/{$themes}/img/weixin_notice_unbind.svg'"
              :alt="row.mp_weixin_notice == 1 ? lang.product_set_text102 : lang.product_set_text103">
          </t-tooltip>
        </template>
        <template #username_company="{row}">
          <t-tooltip :show-arrow="false" :content="calcDevloper(row.developer_type)" theme="light"
            v-if="row.developer_type">
            <svg class="common-look">
              <use xlink:href="/{$template_catalog}/template/{$themes}/img/icon/icons.svg#cus-developer">
              </use>
            </svg>
          </t-tooltip>
          <!-- row.custom_field.length === 0 || !hasPlugin  有以用户登录，取消tooltip -->
          <t-tooltip :content="filterName(row.custom_field)" :show-arrow="false" theme="light" disabled>
            <login-by-user :id="row.id" :website_url="website_url">
              <a :href="`client_detail.htm?client_id=${row.id}`" class="aHover"
                :class="{bg:row.custom_field.length > 0 && hasPlugin}"
                :style="{'background-color': filterColor(row.custom_field), color: calcColor(filterColor(row.custom_field))}"
                v-if="showDetails">
                {{row.username}}
                <span v-if="row.company">({{row.company}})</span>
              </a>
              <span v-else>{{row.username}}<span v-if="row.company">({{row.company}})</span></span>
            </login-by-user>
            <t-tooltip v-show="row.parent_id" :show-arrow="false" theme="light">
              <span @click="goDetail(row.parent_id)" slot="content" style="cursor: pointer">
                #{{row.parent_id}} {{row.parent_name}}
              </span>
              <t-tag>{{lang.user_text17}}</t-tag>
            </t-tooltip>
          </t-tooltip>
        </template>
        <template #host_active_num_host_num="{row}">
          {{row.host_active_num}}({{row.host_num}})
        </template>
        <template #phone="{row}">
          <a v-if="row.phone">+{{row.phone_code}}&nbsp;-&nbsp;{{row.phone}}</a>
          <a v-else>--</a>
        </template>
        <template #reg_time="{row}">
          {{row.reg_time ? moment(row.reg_time * 1000).format('YYYY-MM-DD HH:mm') : ''}}
        </template>
        <template #client_credit="{row}">
          {{currency_prefix}}{{row.credit | filterMoney}}
        </template>
        <template #cost_price="{row}">
          {{currency_prefix}}{{row.cost_price | filterMoney}}
        </template>
        <template #refund_price="{row}">
          {{currency_prefix}}{{row.refund_price | filterMoney}}
        </template>
        <template #withdraw_price="{row}">
          {{currency_prefix}}{{row.withdraw_price | filterMoney}}
        </template>
        <template #client_status="{row}">
          <t-tag theme="primary" class="com-status" v-if="row.status" variant="light">{{lang.enable}}</t-tag>
          <t-tag theme="danger" class="com-status" v-else variant="light">{{lang.deactivate}}</t-tag>
        </template>
        <template #op="{row}">
          <a class="common-look" :href="`client_detail.htm?client_id=${row.id}`">{{lang.look}}</a>
          <a class="common-look" @click="changeStatus(row)">{{row.status ? lang.deactivate : lang.enable}}</a>
          <a class="common-look" @click="deleteUser(row)">{{lang.delete}}</a>
        </template>
        <template #footer-summary>
          <div class="page-total-amount" v-if="total">
            <div class="amount-item">
              {{lang.page_total_credit}}：<span class="amount-num">{{currency_prefix}}{{page_total_credit}}</span>
            </div>
            <div class="amount-item">
              {{lang.total_credit}}：<span class="amount-num">{{currency_prefix}}{{total_credit}}</span>
            </div>
          </div>
        </template>
      </t-table>
      <com-pagination v-if="total" :total="total" :page="params.page" :limit="params.limit" @page-change="changePage">
      </com-pagination>
    </t-card>
    <!-- 添加用户弹窗 -->
    <t-dialog :visible.sync="visible" :header="lang.create_user" :on-close="close" :footer="false" width="600">
      <t-form :rules="rules" :data="formData" ref="userDialog" @submit="onSubmit" name="clientForm">
        <t-form-item :label="lang.name">
          <t-input :placeholder="lang.name" v-model="formData.username" />
        </t-form-item>
        <t-form-item :label="lang.phone" name="phone" :rules="formData.email ?
              [{ required: false},{pattern: /^\d{0,11}$/, message: lang.verify11 }]:
              [{ required: true,message: lang.input + lang.phone, type: 'error' },
              {pattern: /^\d{0,11}$/, message: lang.verify11,type: 'warning' }]">
          <t-select v-model="formData.phone_code" filterable style="width: 100px" :placeholder="lang.phone_code">
            <t-option v-for="item in country" :value="item.phone_code" :label="item.name_zh + '+' + item.phone_code"
              :key="item.name">
            </t-option>
          </t-select>
          <t-input :placeholder="lang.phone" v-model="formData.phone" @change="cancelEmail" />
        </t-form-item>
        <t-form-item :label="lang.email" name="email" class="email" :rules="formData.phone ?
                [{ required: false },
                {pattern: /^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z_])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{1,9})$/,
                message: lang.email_tip, type: 'warning' }]:
                [{ required: true,message: lang.input + lang.email, type: 'error'},
                {pattern: /^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z_])*@(([0-9a-zA-Z])+([-\w]*[0-9a-zA-Z])*\.)+[a-zA-Z]{1,9})$/,
                message: lang.email_tip, type: 'warning' }
                ]">
          <t-input :placeholder="lang.email" v-model="formData.email" @change="cancelPhone"></t-input>
          <p class="tip" v-show="!formData.phone && !formData.email">{{lang.user_tip}}</p>
        </t-form-item>
        <t-form-item :label="lang.password" name="password">
          <t-input :placeholder="lang.password" :type="formData.password ? 'password' : 'text'"
            v-model="formData.password" autocomplete="off" />
        </t-form-item>
        <t-form-item :label="lang.surePassword" name="repassword">
          <t-input :placeholder="lang.surePassword" :type="formData.repassword ? 'password' : 'text'"
            v-model="formData.repassword" autocomplete="off" />
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="close">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>

    <t-dialog :header="lang.data_export" :visible.sync="exportVisible" :footer="false">
      <div style="margin-bottom: 20px;">
        <div>{{lang.data_export_tip2}},{{lang.data_export_tip3}}<span
            style="color: var(--td-brand-color);">{{total}}</span>{{lang.data_export_tip4}}</div>
        <div style="margin-top:20px; color: var(--td-text-color-placeholder);">
          <span style="margin-right: var(--td-comp-margin-xs);
            color: var(--td-error-color);
            line-height: var(--td-line-height-body-medium);">*</span>
          {{lang.data_export_tip5}}
        </div>
        <div style="margin-bottom: 15px; color: var(--td-text-color-placeholder);">
          <span style="margin-right: var(--td-comp-margin-xs);
            color: var(--td-error-color);
            line-height: var(--td-line-height-body-medium);">*</span>
          {{lang.data_export_tip6}}
        </div>
      </div>
      <div class="com-f-btn">
        <t-button theme="primary" type="submit" :loading="exportLoading"
          @click="handelDownload">{{ exportLoading ? lang.data_export_tip8 : lang.data_export_tip7}}</t-button>
        <t-button theme="default" variant="base" @click="exportVisible = false">{{lang.cancel}}</t-button>
      </div>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/components/comViewFiled/comViewFiled.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/loginByUser/loginByUser.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/client.js"></script>
{include file="footer"}

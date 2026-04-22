{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<div id="content" class="transaction order" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="common-header">
        <div class="flex">
          <t-button @click="addFlow" class="add"
            v-permission="'auth_business_transaction_create_transaction'">{{lang.new_flow}}</t-button>
          <t-button theme="default" class="com-gray-btn" :loading="exportLoading" @click="exportVisible = true"
            v-if="$checkPermission('auth_business_transaction_export_excel') && hasExport">
            {{lang.data_export}}
          </t-button>
        </div>
        <!-- 右侧搜索 -->
        <div class="right-search">
          <template v-if="!isAdvance">
            <t-select v-model="params.gateway" :placeholder="lang.pay_way" clearable>
              <t-option v-for="item in payList" :value="item.name" :label="item.title" :key="item.name">
              </t-option>
            </t-select>
            <t-select v-model="params.type" :placeholder="lang.order_type" clearable>
              <t-option v-for="item in orderTypes" :value="item.value" :label="item.label" :key="item.value">
              </t-option>
            </t-select>
            <t-input v-model="params.payment_channel" class="search-input" :placeholder="lang.payment_channels"
              @keypress.enter.native="search" clearable @clear="clearKey('payment_channel')">
            </t-input>
            <t-input v-model="params.client_id" class="search-input" :placeholder="lang.userid"
              @keypress.enter.native="search" clearable @clear="clearKey('client_id')">
            </t-input>
            <div class="com-search">
              <t-input v-model="params.keywords" class="search-input"
                :placeholder="`${lang.flow_number}、${lang.order}ID、${lang.username}、${lang.email}、${lang.phone}`"
                @keypress.enter.native="search" clearable @clear="clearKey('keywords')" style="width: 270px">
              </t-input>
            </div>
            <t-button @click="search" class="search" class="com-gap">{{lang.query}}</t-button>
          </template>
          <t-button @click="changeAdvance" variant="outline" theme="primary"
            class="com-gap">{{isAdvance ? lang.pack_up : lang.advanced_filter}}</t-button>
          <com-view-filed view="transaction" @changefield="changeField"></com-view-filed>
        </div>
      </div>
      <!-- 高级搜索 -->
      <div class="advanced" v-show="isAdvance">
        <div class="search">
          <t-input v-model="params.keywords" class="search-input"
            :placeholder="`${lang.flow_number}、${lang.order}ID、${lang.username}、${lang.email}、${lang.phone}`"
            @keypress.enter.native="search" clearable @clear="clearKey('keywords')">
          </t-input>
          <t-input v-model="params.client_id" style="width: 150px;" :placeholder="lang.userid"
            @keypress.enter.native="search" clearable @clear="clearKey('client_id')">
          </t-input>
          <t-input v-model="params.payment_channel" style="width: 150px;" :placeholder="lang.payment_channels"
            @keypress.enter.native="search" clearable @clear="clearKey('payment_channel')">
          </t-input>
          <t-input :placeholder="lang.money" v-model="params.amount" @keypress.enter.native="search" clearable
            @clear="clearKey('amount')" style="width: 150px;"></t-input>
          <t-select v-model="params.type" :placeholder="lang.order_type" clearable>
            <t-option v-for="item in orderTypes" :value="item.value" :label="item.label" :key="item.value">
            </t-option>
          </t-select>
          <t-select v-model="params.gateway" :placeholder="lang.pay_way" clearable>
            <t-option v-for="item in payList" :value="item.name" :label="item.title" :key="item.name">
            </t-option>
          </t-select>
          <t-date-range-picker allow-input clearable v-model="range"
            :placeholder="[`${lang.flow_date}`,`${lang.flow_date}`]">
          </t-date-range-picker>
        </div>
        <t-button @click="search" :loading="loading" class="com-gap">{{lang.query}}</t-button>
      </div>
      <!-- 高级搜索 end -->
      <t-table row-key="id" :data="calcList" size="medium" :columns="columns" :hover="hover" resizable
        :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange"
        :hide-sort-tips="true">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #username_company="{row}">
          <a :href="`client_detail.htm?client_id=${row?.client_id}`" class="aHover">
            <span v-if="row.client_name">{{row.client_name}}</span>
            <span v-if="row.company">({{row.company}})</span>
          </a>
        </template>
        <template #amount="{row}">
          {{currency_prefix}}&nbsp;{{row.amount}}<span v-if="row.billing_cycle">/</span>{{row.billing_cycle}}
        </template>
        <template #order_id="{row}">
          <span v-if="row.order_id!==0" @click="rowClick(row)" class="aHover">{{row.order_id}}</span>
          <span v-else>--</span>
        </template>

        <template #payment_channel="{row}">
          <template>
            <span>{{row.payment_channel || '--'}}</span>
          </template>
        </template>

        <template #transaction_notes="{row}">
          {{row.transaction_notes || '--'}}
        </template>

        <template #order_type="{row}">
          <template v-if="row.type">
            <img :src="`${rootRul}img/icon/${row.type}.png`" alt="" style="position: relative; top: 3px;">
            {{lang[row.type]}}
          </template>
        </template>
        <template #certification="{row}">
          <t-tooltip :show-arrow="false" theme="light">
            <span slot="content">{{!row.certification ? lang.real_tip8 : row.certification_type === 'person' ?
                      lang.real_tip9 : lang.real_tip10}}</span>
            <t-icon :class="row.certification ? 'green-icon' : ''"
              :name="!row.certification ? 'user-clear': row.certification_type === 'person' ? 'user' : 'usergroup'" />
          </t-tooltip>
        </template>
        <template #client_status="{row}">
          <t-tag theme="success" class="com-status" v-if="row.client_status" variant="light">{{lang.enable}}</t-tag>
          <t-tag theme="danger" class="com-status" v-else variant="light">{{lang.deactivate}}</t-tag>
        </template>
        <template #transaction_time="{row}">
          <span>{{moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}</span>
        </template>
        <template #client_id="{row}">
          <a :href="`client_detail.htm?client_id=${row.client_id}`" class="aHover">{{row.client_id}}</a>
        </template>
        <template #reg_time="{row}">
          {{row.reg_time ? moment(row.reg_time * 1000).format('YYYY-MM-DD HH:mm') : ''}}
        </template>
        <template #hosts="{row}">
          <!-- :href="`host_detail.htm?client_id=${row.client_id}&id=${item.id}`"  -->
          <span v-for="(item,index) in row.hosts" class="aHover" @click="rowClick(row)">
            {{item.name}}
            <span v-if="row.hosts.length>1 && index !== row.hosts.length - 1">、</span>
          </span>
        </template>
        <template #op="{row}">
          <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
            <t-icon name="edit" size="18px" @click="updateFlow(row)" class="common-look"
              v-permission="'auth_business_transaction_update_transaction'"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
            <t-icon name="delete" size="18px" @click="delteFlow(row)" class="common-look"
              v-permission="'auth_business_transaction_delete_transaction'"></t-icon>
          </t-tooltip>
        </template>
        <template #footer-summary>
          <div class="page-total-amount" v-if="total">
            <div class="amount-item">
              {{lang.page_total_amount}}：<span class="amount-num">{{currency_prefix}}{{page_total_amount}}</span>
            </div>
            <div class="amount-item">
              {{lang.total_amount}}：<span class="amount-num">{{currency_prefix}}{{total_amount}}</span>
            </div>
          </div>
        </template>
      </t-table>
      <com-pagination v-if="total" :total="total" :page="params.page" :limit="params.limit" @page-change="changePage">
      </com-pagination>
    </t-card>
    <!-- 新增流水 -->
    <t-dialog :header="optTitle" :visible.sync="flowModel" :footer="false" width="600">
      <t-form :data="formData" ref="form" @submit="onSubmit" :rules="rules" v-if="flowModel">
        <t-form-item :label="lang.user" name="client_id" class="user">
          <com-choose-user :check-id="formData.client_id" :pre-placeholder="lang.example" @changeuser="changeUser">
          </com-choose-user>
        </t-form-item>
        <t-form-item :label="lang.money" name="amount">
          <t-input v-model="formData.amount" type="tel" :label="currency_prefix" :placeholder="lang.money"></t-input>
        </t-form-item>
        <t-form-item :label="lang.pay_way" name="gateway">
          <t-select v-model="formData.gateway" :placeholder="lang.pay_way">
            <t-option v-for="item in payList" :value="item.name" :label="item.title" :key="item.name">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.flow_number" name="transaction_number">
          <t-input v-model="formData.transaction_number" :placeholder="lang.flow_number"></t-input>
        </t-form-item>
        <t-form-item :label="lang.notes" name="notes">
          <t-input v-model="formData.notes" :placeholder="lang.notes"></t-input>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="addLoading">{{lang.submit}}
          </t-button>
          <t-button theme="default" variant="base" @click="flowModel=false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 删除流水提示框 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :close-btn="false" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureDelUser" :loading="addLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
    <!-- 交易流水详情 -->
    <t-dialog :header="lang.flow_detail" :visible.sync="orderVisible" :footer="false" width="1000">
      <t-enhanced-table ref="tableDialog" row-key="id" :data="orderDetail" :columns="orderColumns"
        :tree="{ childrenKey: 'items', treeNodeColumnIndex: 0}" :loading="detailLoading"
        :tree-expand-and-fold-icon="treeExpandAndFoldIconRender" class="user-order" :expandAll="true">
        <template #id="{row}">
          <span v-if="row.type">{{row.id}}</span>
          <!-- <span v-else class="child">-</span> -->
        </template>
        <template #type="{row}">
          {{lang[row.type]}}
        </template>
        <template #create_time="{row}">
          {{row.type ? moment(row.create_time * 1000).format('YYYY/MM/DD HH:mm') : ''}}
        </template>
        <template #product_names={row}>
          <div v-if="row.type">
            <span>{{row.product_names[0]}}</span>
            <span v-if="row.product_names.length>1">、{{row.product_names[1]}}</span>
            <span v-if="row.product_names.length>2">{{lang.wait}}{{row.product_names.length}}个产品</span>
          </div>
          <div v-else>
            <span>{{row.product_name || row.description}}</span>
          </div>
        </template>
        <template #amount="{row}">
          {{currency_prefix}}&nbsp;{{row.amount}}<span v-if="row.billing_cycle">/</span>{{row.billing_cycle}}
        </template>
        <template #status="{row}">
          <t-tag theme="warning" variant="light" v-if="(row.status || row.host_status)==='Unpaid'">{{lang.Unpaid}}
          </t-tag>
          <t-tag theme="primary" variant="light" v-if="row.status==='Paid'">{{lang.Paid}}
          </t-tag>
          <t-tag theme="primary" variant="light" v-if="row.host_status === 'Pending'">
            {{lang.Pending}}
          </t-tag>
          <t-tag theme="success" variant="light" v-if="(row.status || row.host_status)==='Active'">{{lang.Active}}
          </t-tag>
          <t-tag theme="danger" variant="light" v-if="(row.status || row.host_status)==='Failed'">{{lang.Failed}}
          </t-tag>
          <t-tag theme="default" variant="light" v-if="(row.status || row.host_status)==='Suspended'">
            {{lang.Suspended}}
          </t-tag>
          <t-tag theme="default" variant="light" v-if="(row.status || row.host_status)==='Deleted'"
            class="delted">{{lang.Deleted}}
          </t-tag>
        </template>
        <!-- <template #gateway="{row}">
        <template v-if="row.credit == 0 && row.amount !=0">
          {{row.gateway}}
        </template>
        <template v-if="row.credit>0 && row.credit < row.amount">
          <t-tooltip :content="currency_prefix+row.credit" theme="light" placement="bottom-right">
            <span>{{lang.credit}}</span>
          </t-tooltip>
          <span>+{{row.gateway}}</span>
        </template>
        <template v-if="row.credit==row.amount">
          <t-tooltip :content="currency_prefix+row.credit" theme="light" placement="bottom-right">
            <span>{{lang.credit}}</span>
          </t-tooltip>
        </template>
      </template> -->
      </t-enhanced-table>
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
<script src="/{$template_catalog}/template/{$themes}/components/comChooseUser/comChooseUser.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/transaction.js"></script>
{include file="footer"}

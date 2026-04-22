{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="order-details hasCrumb" v-cloak>
  <com-config>
    <div class="com-crumb">
      <span>{{lang.business_manage}}</span>
      <t-icon name="chevron-right"></t-icon>
      <span style="cursor: pointer;" @click="goOrder">{{lang.order_manage}}</span>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.create_order_detail}}</span>
      <span class="back-text" @click="goBack">
        <t-icon name="chevron-left-double"></t-icon>{{lang.back}}
      </span>
    </div>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li class="active"
          v-if="$checkPermission('auth_business_order_detail_order_detail_view') || $checkPermission('auth_user_detail_order_check_order')">
          <a>{{lang.create_order_detail}}</a>
        </li>
        <li v-permission="'auth_business_order_detail_refund_record_view'">
          <a :href="`order_refund.htm?id=${id}`">{{lang.refund_record}}</a>
        </li>
        <li v-permission="'auth_business_order_detail_transaction'">
          <a :href="`order_flow.htm?id=${id}`">{{lang.flow}}</a>
        </li>
        <li v-if="hasCostPlugin" v-permission="'auth_addon_cost_pay_show_tab'">
          <a :href="`plugin/cost_pay/order_cost.htm?id=${id}`">{{lang.piece_cost}}</a>
        </li>
        <li v-permission="'auth_order_detail_info_record_view'">
          <a :href="`order_notes.htm?id=${id}`">{{lang.notes}}</a>
        </li>
      </ul>
      <!-- 基础信息 -->
      <div class="top-info">
        <div class="left-box">
          <div class="item">
            <span class="txt">{{lang.order_number}}：</span>
            <span>{{id}}</span>
          </div>
          <div class="item">
            <span class="txt">{{lang.order_type}}：</span>
            <span>{{lang[orderDetail.type]}}</span>
          </div>
          <div class="item">
            <span class="txt">{{lang.user}}：</span>
            <a :href="`client_detail.htm?client_id=${orderDetail.client_id}`"
              class="info aHover">{{orderDetail.client_name}}</a>
          </div>
          <div class="item">
            <span class="txt">{{lang.order + lang.time}}：</span>
            <span class="info">
              {{moment(orderDetail.create_time * 1000).format('YYYY-MM-DD HH:mm')}}
            </span>
          </div>
          <div class="item">
            <span class="txt">{{lang.order}}{{lang.money}}：</span>
            <span class="info">{{currency_prefix}}&nbsp;{{orderDetail.amount}}</span>
          </div>
          <div class="item">
            <span class="txt">{{lang.order_detail_text1}}：</span>
            <span class="info">{{currency_prefix}}&nbsp;{{orderDetail.credit}}</span>
            <span class="btn" @click="changeCredit('add')" v-if="(orderDetail.amount * 1 !== orderDetail.credit * 1) && orderDetail.apply_credit_amount * 1 > 0
              && $checkPermission('auth_business_order_detail_order_detail_apply_credit') && orderDetail.is_recycle === 0
              && !isTransfer
              ">{{lang.app}}{{lang.credit}}</span>
            <span class="btn" @click="changeCredit('sub')"
              v-if="orderDetail.status == 'Unpaid' && orderDetail.credit * 1 !== 0 && $checkPermission('auth_business_order_detail_order_detail_remove_credit') && orderDetail.is_recycle === 0">{{lang.deduct}}{{lang.credit}}</span>
          </div>
          <div class="item">
            <span class="txt">{{lang.refunded}}：</span>
            <span class="info">{{currency_prefix}}&nbsp;{{orderDetail.refund_amount}}
              <span v-if="orderDetail.refund_gateway*1 > 0">({{orderDetail.gateway_sign === 'credit_limit' ?
                lang.order_detail_text30 :
                lang.order_detail_text10}}：{{currency_prefix}}&nbsp;{{orderDetail.refund_gateway}})</span>
            </span>
            <span class="btn" @click="changeLog"
              v-permission="'auth_business_order_detail_order_detail_change_log'">{{lang.change_log}}</span>
          </div>
          <div class="item" v-if="orderDetail.type === 'artificial'">
            <span class="txt">{{lang.operator}}：</span>
            <span class="info">{{orderDetail.admin_name || '--'}}</span>
          </div>
          <div class="item" v-for="item in self_defined_field" :key="item.id">
            <span class="txt">{{item.field_name}}:</span>
            <span class="info">{{item.value || '--'}}</span>
          </div>
        </div>
        <div class="r-box">
          <div class="con">
            <div class="top">
              <div class="status">
                <t-tag theme="default" variant="light" class="order-canceled"
                  v-if="orderDetail.status === 'Cancelled'">{{lang.canceled}}</t-tag>
                <t-tag theme="default" variant="light" class="order-paid"
                  v-if="orderDetail.status === 'Paid'">{{lang.Paid}}</t-tag>
                <t-tag theme="default" variant="light" class="order-refunded"
                  v-if="orderDetail.status === 'Refunded'">{{lang.refunded}}</t-tag>
                <t-tag theme="default" variant="light" class="order-unpaid"
                  v-if="orderDetail.status === 'Unpaid'">{{lang.Unpaid}}</t-tag>
              </div>
              <!-- 是否开票 -->
              <div class="is-invoiced" v-if="hasInvoicePlugin">
                <a :href="`${baseUrl}/plugin/idcsmart_invoice/index.htm?id=${id}`" target="_blank"
                  v-if="invoiceObj.invoice.id">
                  <t-tag theme="primary">
                    {{lang.order_invoiced}}
                  </t-tag>
                </a>
                <template v-esle>
                  <template v-if="invoiceObj.support_invoice">
                    <t-tag>{{lang.order_not_invoiced}}</t-tag>
                    <a :href="`${baseUrl}/plugin/idcsmart_invoice/index.htm?id=${id}&client_id=${orderDetail.client_id}&status=${orderDetail.status}`"
                      class="aHover common-look" target="_blank" style="margin-left: 5px;">
                      {{lang.order_go_invoice}}
                    </a>
                  </template>
                </template>
              </div>
            </div>
            <div class="invoiced-open" v-if="hasInvoicePlugin && invoiceObj.support_invoice">
              <span>{{lang.order_invoiced_open}}</span>
              <t-switch size="medium" :custom-value="[0,1]" :loading="invoiceLoading"
                v-model="invoiceObj.allow_home_invoice_create" @change="changeInvoice"></t-switch>
            </div>
            <template v-if="orderDetail.status === 'Unpaid'">
              <t-select v-model="gateway" :placeholder="lang.pay_way" class="order-pay" @change="changePay">
                <t-option v-for="item in payList" :value="item.name" :label="item.title" :key="item.name">
                </t-option>
              </t-select>
              <div class="signPay"
                v-if="$checkPermission('auth_business_order_detail_order_detail_paid') && orderDetail.is_recycle === 0">
                <t-button theme="primary" @click="signPay">{{lang.sign_pay}}
                </t-button>
              </div>
            </template>
            <p class="time">
              <template v-if="orderDetail.status === 'Paid'">
                {{moment(orderDetail.pay_time * 1000).format('YYYY-MM-DD HH:mm')}}
              </template>
              <template v-else>
                {{moment(orderDetail.create_time * 1000).format('YYYY-MM-DD HH:mm')}}
              </template>
            </p>
            <!-- 支付方式 -->
            <p class="gateway">
              <template v-if="orderDetail.status === 'Unpaid'">
                --
              </template>
              <template v-else>
                <template v-if="orderDetail.gateway_sign === 'credit'">
                  <span>{{lang.balance_pay}}</span>
                </template>
                <template v-else>
                  <!-- 其他支付方式 -->
                  <template v-if="orderDetail.credit == 0">
                    {{orderDetail.gateway}}
                  </template>
                  <!-- 混合支付 -->
                  <template v-if="orderDetail.credit * 1 >0">
                    <t-tooltip :content="currency_prefix+orderDetail.credit" theme="light" placement="bottom-right">
                      <span class="theme-color">{{lang.balance_pay}}</span>
                    </t-tooltip>
                    <span>{{orderDetail.gateway ? '+ ' + orderDetail.gateway: '' }}</span>
                  </template>
                </template>

              </template>
            </p>
          </div>
        </div>
      </div>

      <template v-if="orderDetail.status">
        <template v-if="orderDetail.status ==='Paid' || orderDetail.status ==='Refunded'">
          <div class="refund-top">
            <div>
              <!-- 充值也可退款 -->
              <t-button
                v-if="((orderDetail.type === 'recharge' && recharge_order_support_refund) || orderDetail.type !== 'recharge') && $checkPermission('auth_business_order_detail_refund_record_approval')"
                theme="primary" @click="handleRefund">{{lang.order_detail_text16}}
              </t-button>
            </div>
            <t-input @keypress.enter.native="getProductRefundList" clearable @clear="clearKey" style="width: 300px;"
              :placeholder="lang.order_detail_text17" v-model="refundKeywords">
              <template #suffixIcon>
                <t-icon @click="getProductRefundList" name="search" :style="{ cursor: 'pointer' }"></t-icon>
              </template>
            </t-input>
          </div>
          <!-- 退款列表： 新增展示折扣信息，总体订单-展开是详情 -->
          <t-enhanced-table row-key="key" :data="productRefundData" size="medium" :columns="refundColumns"
            :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" :hide-sort-tips="true"
            :key="expandNum" :tree="{ treeNodeColumnIndex: 0, expandTreeNodeOnClick: true }" :default-expand-all="true"
            @expanded-tree-nodes-change="changeExpand" :expanded-tree-nodes="expandedRowKeys"
            :default-expanded-row-keys="expandedRowKeys">
            <template slot="sortIcon">
              <t-icon name="caret-down-small"></t-icon>
            </template>
            <template #description="{row}">
              <a :href="`host_detail.htm?client_id=${orderDetail.client_id}&id=${row.host_id}`" class="aHover"
                v-if="row.host_id">{{row.description}}</a>
              <span v-else>{{row.description}}</span>
            </template>
            <template #product_name="{row}">
              <a :href="`host_detail.htm?client_id=${orderDetail.client_id}&id=${row.host_id}`" class="aHover"
                v-if="row.host_id">{{row.product_name || '--'}}</a>
              <span v-else>{{row.product_name || '--'}}</span>
            </template>
            <template #host_name="{row}">
              <template v-if="row.ip_num > 0">
                <t-tooltip :show-arrow="false" theme="light">
                  <template #content>
                    <div>
                      <div v-if="row.dedicate_ip">{{row.dedicate_ip}}</div>
                      <div v-if="row.assign_ip">
                        <div v-for="item in row.assign_ip.split(',')">{{item}}</div>
                      </div>
                    </div>
                  </template>
                  <span>{{row.host_name || '--'}}</span>
                </t-tooltip>
              </template>
              <template v-else>
                <span>{{row.host_name || '--'}}</span>
              </template>
            </template>
            <template #amount="{row}">
              <span>{{currency_prefix}}{{row.amount}}</span>
            </template>
            <template #refund_status="{row}">
              <span>{{refunStatusOptions[row.refund_status] || '--'}}</span>
            </template>
            <template #host_status="{row}">
              <span>{{hostStatusOptions[row.host_status] || '--'}}</span>
            </template>
            <template #refund_total="{row}">
              <div style="white-space:normal;" v-if="row.key.indexOf('child_') === -1">
                <div v-if="row.refund_total * 1  !== 0">{{currency_prefix}}{{row.refund_total}}</div>
                <div v-else>--</div>
                <template v-if="orderDetail.gateway_sign === 'credit_limit'">
                  <span
                    v-if="row.refund_gateway * 1  !== 0">({{currency_prefix}}{{row.refund_gateway}}{{lang.order_detail_text30}})</span>
                </template>
                <template v-else>
                  <div v-if="row.refund_credit * 1  !== 0 || row.refund_gateway * 1  !== 0" style="white-space:normal;">
                    (
                    <span
                      v-if="row.refund_credit * 1  !== 0">{{currency_prefix}}{{row.refund_credit}}{{lang.order_detail_text11}}</span>
                    <span v-if="row.refund_gateway * 1  !== 0">
                      <span v-if="row.refund_credit * 1  !== 0">+</span>
                      {{currency_prefix}}{{row.refund_gateway}}{{lang.order_detail_text10}}
                    </span>)
                  </div>
                </template>
              </div>
              <span v-else>--</span>
            </template>
            <template #refund_credit="{row}">
              <template v-if="row.key.indexOf('child_') === -1">
                <template v-if="orderDetail.gateway_sign === 'credit_limit'">
                  <span v-if="row.refund_total * 1  !== 0">{{lang.order_detail_text30}}</span>
                  <span v-else>--</span>
                </template>
                <template v-else>
                  <span v-if="row.refund_credit * 1  !== 0 || row.refund_gateway * 1  !== 0">
                    <span v-if="row.refund_credit * 1  !== 0">{{lang.order_detail_text11}}</span>
                    <template v-if="row.refund_gateway * 1  !== 0">
                      <span v-if="row.refund_credit * 1  !== 0">+</span>
                      <span>{{lang.order_detail_text10}}</span>
                    </template>
                  </span>
                  <span v-else>--</span>
                </template>
              </template>
              <span v-else>--</span>
            </template>
            <template #op="{row, rowIndex}">
              <!-- <span class="common-look"
                v-if="row.refund_status !== 'addon_refund' && row.refund_status !== 'all_refund' && row.host_id && $checkPermission('auth_business_order_detail_refund_record_approval')"
                @click="handleRefund(row)">{{lang.order_detail_text9}}</span> -->
              <t-tooltip :content="lang.order_detail_text9" :show-arrow="false" theme="light"
                v-if="row.refund_status !== 'addon_refund' && row.refund_status !== 'all_refund' && row.host_id && $checkPermission('auth_business_order_detail_refund_record_approval')">
                <i class="iconfont icon-daichulituikuan" @click="handleRefund(row)"></i>
              </t-tooltip>
              <span v-else>--</span>
            </template>
          </t-enhanced-table>
        </template>
        <template v-else>
          <!-- 底部描述 -->
          <t-table row-key="id" :data="orderDetail.items" size="medium" :columns="columns" :hover="hover"
            :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" :hide-sort-tips="true">
            <template slot="sortIcon">
              <t-icon name="caret-down-small"></t-icon>
            </template>
            <template #description="{row}">
              <t-input v-model="row.description" v-if="row.edit"></t-input>
              <template v-else>
                <a :href="`host_detail.htm?client_id=${orderDetail.client_id}&id=${row.host_id}`" class="aHover"
                  v-if="row.host_id">{{row.description}}</a>
                <span v-else>{{row.description}}</span>
              </template>
            </template>
            <template #product_name="{row}">
              <a :href="`host_detail.htm?client_id=${orderDetail.client_id}&id=${row.host_id}`" class="aHover"
                v-if="row.host_id">{{row.product_name || '--'}}</a>
              <span v-else>{{row.product_name || '--'}}</span>
            </template>
            <template #host_name="{row}">
              <span>{{row.host_name || '--'}}</span>
            </template>
            <template #amount="{row}">
              <t-input v-model="row.amount" :label="currency_prefix" v-if="row.edit"></t-input>
              <span v-else>{{currency_prefix}}{{row.amount}}</span>
            </template>
            <template #op="{row, rowIndex}">
              <template v-if="orderDetail.status === 'Unpaid' && orderDetail.is_recycle === 0">
                <t-tooltip :content="lang.delete" :show-arrow="false" theme="light"
                  v-if="row.edit && $checkPermission('auth_business_order_detail_order_detail_delete_order_item')">
                  <t-icon name="delete" size="18px" @click="delteFlow(row, rowIndex)" class="common-look"></t-icon>
                </t-tooltip>
                <t-tooltip :content="lang.hold" :show-arrow="false" theme="light"
                  v-if="row.edit && $checkPermission('auth_business_order_detail_order_detail_save_order_item')">
                  <t-icon name="save" size="18px" @click="saveFlow(row)" class="common-look"></t-icon>
                </t-tooltip>
                <t-tooltip :content="lang.add" :show-arrow="false" theme="light"
                  v-if="rowIndex === orderDetail.items.length -1 && $checkPermission('auth_business_order_detail_order_detail_create_order_item')">
                  <t-icon name="add-circle" size="18px" @click="addSubItem(row)" class="common-look"></t-icon>
                </t-tooltip>
              </template>
            </template>
          </t-table>
        </template>
      </template>
      <!-- 订单退款弹窗 -->
      <t-dialog :visible.sync="refundVisible"
        :header="refundFormData.host_id ? lang.order_detail_text28 : lang.order_detail_text16" :on-close="refundClose"
        :footer="false" width="700" :close-on-overlay-click="false" reset-type="initial">



        <t-form :rules="refundRules" :data="refundFormData" ref="refundDialog" @submit="refundSubmit"
          :label-width="150">
          <div
            style="color: var(--td-error-color);font-weight: bold; margin-bottom: var(--td-comp-margin-xxl); text-align: center;"
            v-if="hasInvoicePlugin && invoiceObj?.invoice?.id">
            {{lang.order_refund_invoice_tip}}
          </div>

          <t-table style="margin-bottom: var(--td-comp-margin-xxl);" v-if="refundInfo.host_order_item?.length > 0"
            row-key="id" :data="refundInfo.host_order_item" size="medium" :columns="hostColumns" :hover="hover"
            :table-layout="tableLayout ? 'auto' : 'fixed'">
            <template #product_name="{row}">
              <span>{{row.product_name || '--'}}</span>
            </template>
            <template #name="{row}">
              <template v-if="row.description">
                <t-tooltip :content="row.description" :show-arrow="false" theme="light">
                  <span>{{row.name || '--'}}</span>
                </t-tooltip>
              </template>
              <template v-else>
                <span>{{row.name || '--'}}</span>
              </template>
            </template>
            <template #amount="{row}">
              <span>{{currency_prefix}}{{row.amount}}</span>
            </template>
            <template #status="{row}">
              <span>{{hostStatusOptions[row.status] || '--'}}</span>
            </template>
          </t-table>
          <t-form-item :label="lang.order_detail_text19">
            <div>{{currency_prefix}} {{refundInfo.leave_total}}
              <template v-if="refundInfo.gateway === 'credit_limit'">
                ({{lang.order_detail_text30}}:{{currency_prefix}} {{refundInfo.leave_total}})
              </template>
              <template
                v-if="(refundInfo.leave_credit *1 !== 0 || refundInfo.leave_gateway *1 !== 0 )&& refundInfo.gateway !== 'credit_limit'">
                (<span v-if="refundInfo.leave_credit *1 !== 0">{{lang.order_detail_text11}}:{{currency_prefix}}
                  {{refundInfo.leave_credit}}
                </span>
                <span v-if="refundInfo.leave_gateway *1 !== 0"> <span v-if="refundInfo.leave_credit *1 !== 0">+</span>
                  {{lang.order_detail_text10}}:{{currency_prefix}}{{refundInfo.leave_gateway}}
                </span>)
              </template>
            </div>
          </t-form-item>
          <t-form-item :label="lang.order_detail_text29" v-if="refundFormData.host_id">
            <div>{{currency_prefix}} {{refundInfo.leave_host_amount}}</div>
          </t-form-item>
          <t-form-item :label="lang.order_detail_text7" name="amount">
            <t-input-number style="width: 100%;" v-model="refundFormData.amount" theme="normal"
              :allow-input-over-limit="false"
              :max="refundFormData.host_id &&  refundInfo.gateway !== 'credit_limit' ? refundInfo.leave_host_amount * 1 : refundInfo.leave_total * 1"
              :min="0" :decimal-places="2">
            </t-input-number>
          </t-form-item>
          <t-form-item :label="lang.order_detail_text20" name="type" v-if="refundInfo.gateway != 'credit_limit'">
            <t-select v-model="refundFormData.type">
              <t-option key="credit_first"
                v-if="orderDetail.refund_orginal == 1 && refundInfo.leave_gateway * 1 > 0 && orderDetail.type !== 'recharge'"
                :label="lang.order_detail_text21" value="credit_first"></t-option>
              <t-option key="gateway_first" v-if="orderDetail.refund_orginal == 1 && refundInfo.leave_gateway * 1 > 0"
                :label="lang.order_detail_text22" value="gateway_first"></t-option>
              <t-option key="credit" :label="lang.order_detail_text23" value="credit"
                v-if="orderDetail.type !== 'recharge'"></t-option>
              <t-option key="transaction" v-if="refundInfo.leave_gateway * 1 > 0" :label="lang.order_detail_text24"
                value="transaction"></t-option>
            </t-select>
          </t-form-item>
          <template v-if="refundFormData.type === 'transaction'">
            <t-form-item :label="lang.gateway" name="gateway">
              <t-select v-model="refundFormData.gateway" :placeholder="lang.select+lang.gateway">
                <t-option v-for="item in payList" :value="item.name" :label="item.title" :key="item.name">
                </t-option>
              </t-select>
            </t-form-item>
          </template>
          <t-form-item :label="lang.order_detail_text26" v-if="refundFormData.amount && refundFormData.type">
            <div>{{currency_prefix}} {{(refundFormData.amount * 1).toFixed(2)}}
              <template v-if="refundFormData.type !== 'transaction' && refundInfo.gateway != 'credit_limit'">
                (
                <span v-if="calcRefundCredit *1 !== 0">{{lang.order_detail_text11}}:{{currency_prefix}}
                  {{calcRefundCredit.toFixed(2)}}
                </span>
                <span v-if="calcRefundCredit * 1 < refundFormData.amount*1">
                  <span v-if="calcRefundCredit *1 !== 0">+</span>
                  {{lang.order_detail_text10}}:{{currency_prefix}}
                  {{calcGetaway.toFixed(2)}}
                </span>
                )
              </template>
              <template v-if="refundInfo.gateway === 'credit_limit'">
                ({{lang.order_detail_text30}}:{{currency_prefix}}{{refundFormData.amount}})
              </template>
            </div>
          </t-form-item>
          <t-form-item :label="lang.order_detail_text25" name="notes">
            <t-textarea v-model="refundFormData.notes" :placeholder="lang.order_detail_text25"></t-textarea>
          </t-form-item>
          <div class="com-f-btn">
            <t-button theme="primary" type="submit" :loading="refundLoading">{{lang.hold}}</t-button>
            <t-button theme="default" variant="base" @click="refundClose">{{lang.cancel}}</t-button>
          </div>
        </t-form>
      </t-dialog>
      <!-- 应用/扣除余额 -->
      <t-dialog :visible.sync="visible" :header="title" :on-close="close" :footer="false" width="600"
        :close-on-overlay-click="false" class="apply-money">
        <t-form :rules="rules" :data="formData" ref="userDialog" @submit="onSubmit" v-if="visible" :label-width="150">
          <t-form-item :label="`${lang.order_tip1}`" name="amount" v-if="type === 'add'">
            <t-input :placeholder="`${lang.input}${lang.money}`" v-model="formData.amount" @blur="changeAdd" />
          </t-form-item>
          <t-form-item :label="`${lang.order_tip2}`" name="amount" v-if="type === 'sub'">
            <t-input :placeholder="`${lang.input}${lang.money}`" v-model="formData.amount" @blur="changeSub" />
          </t-form-item>
          <t-form-item :label="`${lang.box_title3}`" name="status"
            v-if="type === 'add' && orderDetail.status === 'Refunded'">
            <t-select v-model="formData.status">
              <t-option key="Refunded" :label="lang.refunded" value="Refunded"></t-option>
              <t-option key="Paid" :label="lang.Paid" value="Paid"></t-option>
            </t-select>
          </t-form-item>
          <div class="com-f-btn">
            <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
            <t-button theme="default" variant="base" @click="close">{{lang.cancel}}</t-button>
          </div>
        </t-form>
      </t-dialog>
      <!-- 标记支付 -->
      <t-dialog :header="lang.sign_pay" :visible.sync="payVisible" width="600" class="sign_pay">
        <template slot="body">
          <t-form :data="signForm">
            <t-form-item :label="lang.order_amount">
              <t-input :label="currency_prefix" v-model="signForm.amount" disabled />
            </t-form-item>
            <!-- <t-form-item :label="lang.balance_paid">
            <t-input :label="currency_prefix" v-model="signForm.credit" disabled />
          </t-form-item> -->
            <t-form-item :label="lang.no_paid">
              <t-input :label="currency_prefix" v-model="(signForm.credit * 1).toFixed(2)" disabled />
            </t-form-item>
            <t-form-item :label="lang.flow">
              <t-input v-model="signForm.transaction_number"></t-input>
            </t-form-item>
            <!-- <t-checkbox v-model="use_credit" class="checkDelete">{{lang.use_credit}}</t-checkbox> -->
          </t-form>
        </template>
        <template slot="footer">
          <div class="common-dialog" style="margin-top: 20px;">
            <t-button @click="sureSign" :loading="payLoading">{{lang.sure}}</t-button>
            <t-button theme="default" @click="payVisible=false">{{lang.cancel}}</t-button>
          </div>
        </template>
      </t-dialog>
      <!-- 删除提示框 -->
      <t-dialog theme="warning" :header="lang.sureDelete" :close-btn="false" :visible.sync="delVisible">
        <template slot="footer">
          <t-button theme="primary" @click="sureDelUser" :loading="submitLoading">{{lang.sure}}</t-button>
          <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
        </template>
      </t-dialog>
      <!-- 变更记录 -->
      <t-dialog :visible="visibleLog" :header="lang.change_log" :footer="false" :on-close="closeLog" width="1000">
        <div slot="body">
          <t-table row-key="change_log" :data="logData" size="medium" :columns="logColumns" :hover="hover"
            :loading="moneyLoading" table-layout="fixed" max-height="350">
            <template #type="{row}">
              {{lang[row.type]}}
            </template>
            <template #amount="{row}">
              <span>
                <span v-if="row.amount * 1 > 0">+</span>{{row.amount}}
              </span>
            </template>
            <template #create_time="{row}">
              {{moment(row.create_time * 1000).format('YYYY/MM/DD HH:mm')}}
            </template>
            <template #admin_name="{row}">
              {{row.admin_name ? row.admin_name : formData.username}}
            </template>
          </t-table>
          <com-pagination v-if="logCunt" :total="logCunt" :page="moneyPage.page" :limit="moneyPage.limit"
            @page-change="changePage">
          </com-pagination>
        </div>
      </t-dialog>
    </t-card>
    <div class="deleted-svg" v-if="orderDetail.is_recycle">
      <img :src="`${rootRul}img/deleted.svg`" alt="" v-show="orderDetail.is_recycle">
    </div>
  </com-config>
</div>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/order_details.js"></script>
{include file="footer"}

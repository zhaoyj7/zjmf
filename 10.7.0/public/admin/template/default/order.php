{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/viewer.min.css">
<div id="content" class="order" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="common-header">
        <div class="left flex">
          <t-button @click="addOrder" class="add"
            v-permission="'auth_business_order_create_order'">{{lang.create_order}}</t-button>
          <!-- 批量删除 -->
          <t-button @click="batchDel" class="add" theme="danger"
            v-permission="'auth_business_order_batch_delete_order'">{{lang.batch_dele}}</t-button>
          <!-- 回收站 -->
          <t-button @click="openRecyle" class="add com-gray-btn" theme="default"
            v-if="recycleConfig.order_recycle_bin == '0' && $checkPermission('auth_business_order_enable_recycle_bin')">
            {{lang.open_recycle_bin}}
          </t-button>
          <t-button @click="goRecycle" class="add com-gray-btn" theme="default"
            v-if="recycleConfig.order_recycle_bin == '1' && $checkPermission('auth_business_order_recycle_bin_view')">
            {{lang.recycle_bin}}
          </t-button>
          <t-button theme="default" class="com-gray-btn" @click="exportVisible = true"
            v-if="$checkPermission('auth_business_order_export_excel') && hasExport">
            {{lang.data_export}}
          </t-button>
        </div>
        <!-- 右侧搜索 -->
        <div class="right-search">
          <div class="view-filed">
            <!-- 选择视图 -->
            <t-tooltip :show-arrow="false" theme="light" placement="top-left" overlay-class-name="view-change-tip">
              <template slot="content">
                <p>{{lang.field_tip1}}</p>
                <p>{{lang.field_tip2}}</p>
                <p>{{lang.field_tip3}}</p>
                <p>{{lang.field_tip4}}</p>
              </template>
              <t-icon name="help-circle" class="view-tip"></t-icon>
            </t-tooltip>
            <t-select :value="params.view_id" :label="lang.view_filed + ':'" class="choose-view not-default"
              @change="chooseView" :popup-props="{ overlayClassName: `view-select`}">
              <t-option v-for="item in admin_view_list" :value="item.id" :label="item.name" :key="item.id">
              </t-option>
              <div class="bot-opt" slot="panelBottomContent">
                <t-option key="new" value="new" :label="lang.field_add_view"></t-option>
                <t-option key="manage" value="manage" :label="lang.field_manage_view"></t-option>
              </div>
            </t-select>
            <!-- 选择视图 end -->
            <t-select v-model="searchType" class="com-list-type" @change="changeType" filterable>
              <t-option v-for="item in calcTypeSelect" :value="item.value" :label="item.label"
                :key="item.value"></t-option>
            </t-select>
            <t-input v-model="params.keywords" class="search-input" :placeholder="lang.input"
              @keypress.enter.native="search" clearable v-show="searchType !== 'product_id' && searchType !== 'sale'"
              @clear="clearKey('keywords')" :maxlength="30">
            </t-input>
            <input class="com-empty-input"></input>
            <com-tree-select class="search-input goods-select" :autowidth="true" :multiple="true"
              v-show="searchType === 'product_id'" :value="params.product_ids" @choosepro="choosePro">
            </com-tree-select>
            <!-- 选择销售 -->
            <t-select v-model="curSaleId" :placeholder="lang.please_choose_sale" clearable
              v-show="hasSale && searchType === 'sale'">
              <t-option v-for="item in allSales" :value="item.id" :label="item.name" :key="item.name">
              </t-option>
            </t-select>
            <t-button @click="search" v-if="!isAdvance">{{lang.query}}</t-button>
          </div>
          <!-- <div class="view-filed" v-if="isAdvance" style="margin-right: -10px;">
            <t-tooltip :show-arrow="false" theme="light" placement="top-left" overlay-class-name="view-change-tip">
              <template slot="content">
                <p>{{lang.field_tip1}}</p>
                <p>{{lang.field_tip2}}</p>
                <p>{{lang.field_tip3}}</p>
                <p>{{lang.field_tip4}}</p>
              </template>
              <t-icon name="help-circle" class="view-tip"></t-icon>
            </t-tooltip>
            <t-select :value="params.view_id" :label="lang.view_filed + ':'" class="choose-view not-default"
              @change="chooseView" :popup-props="{ overlayClassName: `view-select`}">
              <t-option v-for="item in admin_view_list" :value="item.id" :label="item.name" :key="item.id">
              </t-option>
              <div class="bot-opt" slot="panelBottomContent">
                <t-option key="new" value="new" :label="lang.field_add_view"></t-option>
                <t-option key="manage" value="manage" :label="lang.field_manage_view"></t-option>
              </div>
            </t-select>
          </div> -->
          <t-button @click="changeAdvance" variant="outline" theme="primary" class="ml8"
            :style="{ 'margin-left': isAdvance ? 0 : '16px' }">{{isAdvance ? lang.pack_up : lang.advanced_filter}}</t-button>
          <com-view-filed view="order" @changefield="changeField" ref="customFiled"></com-view-filed>
        </div>
      </div>
      <!-- 高级搜索 -->
      <div class="advanced" v-show="isAdvance">
        <div class="edit-view">
          <t-button class="add" v-if="viewFiledNum && data_range_switch"
            @click="handleEditView">{{lang.view_data_range}}({{viewFiledNum}})</t-button>
        </div>
        <div class="search">
          <!-- <t-select v-model="searchType" class="com-list-type" @change="changeType">
            <t-option v-for="item in typeOption" :value="item.value" :label="item.label" :key="item.value"></t-option>
          </t-select>
          <t-input v-model="params.keywords" class="search-input" :placeholder="lang.input"
            @keypress.enter.native="search" clearable v-show="searchType !== 'product_id'"
            @clear="clearKey('keywords')">
          </t-input>
          <com-tree-select class="search-input" v-show="searchType === 'product_id'" :value="params.product_id"
            @choosepro="choosePro">
          </com-tree-select> -->
          <t-input v-model="params.client_id" class="search-input" :placeholder="`${lang.input}${lang.userid}`"
            @keypress.enter.native="search" clearable @clear="clearKey('client_id')">
          </t-input>
          <t-input :placeholder="lang.money" v-model="params.amount" @keypress.enter.native="search" clearable
            @clear="clearKey('amount')" style="width: 150px;"></t-input>
          <t-date-range-picker allow-input clearable v-model="range"
            :placeholder="[`${lang.order_date}`,`${lang.order_date}`]">
          </t-date-range-picker>
          <t-date-range-picker allow-input clearable v-model="range2"
            :placeholder="[`${lang.pay_time}`,`${lang.pay_time}`]">
          </t-date-range-picker>
          <t-select v-model="params.type" :placeholder="lang.order_type" clearable>
            <t-option v-for="item in orderTypes" :value="item.value" :label="item.label" :key="item.value">
            </t-option>
          </t-select>
          <t-select v-model="params.status" :placeholder="lang.box_title3" clearable>
            <t-option v-for="item in orderStatus" :value="item.value" :label="item.label" :key="item.value">
            </t-option>
          </t-select>
          <t-select v-model="params.gateway" multiple :min-collapsed-num="1" :placeholder="lang.pay_way" clearable>
            <t-option value="Credit" :label="lang.balance_pay" key="credit"></t-option>
            <t-option v-for="item in payWays" :value="item.name" :label="item.title" :key="item.name">
            </t-option>
          </t-select>
          <!-- 选择销售 -->
          <t-select v-model="curSaleId" :placeholder="lang.please_choose_sale" clearable v-show="hasSale">
            <t-option v-for="item in allSales" :value="item.id" :label="item.name" :key="item.name">
            </t-option>
          </t-select>
          <t-button @click="search">{{lang.query}}</t-button>
        </div>
      </div>
      <!-- 高级搜索 end -->
      <!-- 
        卡顿去掉：t-enhanced-table 
          :tree="{ childrenKey: 'list', treeNodeColumnIndex: 0 }"
          :tree-expand-and-fold-icon="treeExpandAndFoldIconRender"
      -->
      <t-table ref="table" row-key="id" drag-sort="row-handler" :data="calcList" :columns="columns" resizable
        :loading="loading" :hover="hover" @sort-change="sortChange" class="user-order"
        :hide-sort-tips="true" @select-change="rehandleSelectChange" :selected-row-keys="checkId"
        @column-resize-change="resizeChange">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #sale="{row}">
          <span>{{row.sale || '--'}}</span>
        </template>
        <template #id="{row}">
          <span @click="lookDetail(row)" v-if="row.type && $checkPermission('auth_business_order_check_order')"
            class="aHover">
            {{row.id}}
          </span>
          <span v-if="row.type && !$checkPermission('auth_business_order_check_order')">{{row.id}}</span>
          <span v-if="!row.type" class="child">-</span>
        </template>
        <template #type="{row}">
          {{lang[row.type]}}
        </template>
        <template #username_company="{row}">
          <t-tooltip :show-arrow="false" :content="calcDevloper(row.developer_type)" theme="light"
            v-if="row.developer_type">
            <svg class="common-look">
              <use xlink:href="/{$template_catalog}/template/{$themes}/img/icon/icons.svg#cus-developer">
              </use>
            </svg>
          </t-tooltip>
          <login-by-user :id="row.client_id" :website_url="website_url">
            <a :href="`client_detail.htm?client_id=${row.client_id}`" class="aHover">
              <span v-if="row.client_name">{{row.client_name}}</span>
              <span v-if="row.company">({{row.company}})</span>
            </a>
          </login-by-user>
        </template>
        <template #order_time="{row}">
          {{row.type ? moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm') : ''}}
        </template>

        <template #pay_time="{row}">
          {{row.pay_time ? moment(row.pay_time * 1000).format('YYYY-MM-DD HH:mm') : '--'}}
        </template>
        <template #order_type="{row}">
          <template v-if="row.type">
            <!-- <t-tooltip :content="lang[row.type]" theme="light" :show-arrow="false" placement="top-right">
              <img :src="`${rootRul}img/icon/${row.type}.png`" alt="" style="position: relative; top: 3px;">
            </t-tooltip> -->
            <img :src="`${rootRul}img/icon/${calcOrderIcon(row.type)}.png`" alt=""
              style="position: relative; top: 3px;">
            {{lang[row.type]}}
          </template>
        </template>
        <template #order_use_credit="{row}">
          {{currency_prefix}}&nbsp;{{row.credit}}
        </template>
        <template #order_refund_amount="{row}">
          {{currency_prefix}}&nbsp;{{row.refund_amount}}
        </template>
        <template #reg_time="{row}">
          {{row.reg_time ? moment(row.reg_time * 1000).format('YYYY-MM-DD HH:mm') : ''}}
        </template>
        <template #client_id="{row}">
          <a :href="`client_detail.htm?client_id=${row.client_id}`" class="aHover"
            v-if="showDetails">{{row.client_id}}</a>
          <span v-else>{{row.client_id}}</span>
        </template>
        <template #product_name={row}>
          <template v-if="row.product_names">
            <template v-if="row.description && $checkPermission('auth_business_order_check_order')">
              <t-tooltip theme="light" :show-arrow="false" placement="top-right">
                <div slot="content" class="tool-content">{{row.description}}</div>
                <!--  <span @click="itemClick(row)" class="hover">{{row.product_names[0]}}</span> -->
                <span class="aHover" @click="lookDetail(row)"
                  v-if="$checkPermission('auth_business_order_check_order')">{{row.product_names[0]}}</span>
                <span v-else>{{row.product_names[0]}}</span>
                <span v-if="row.product_names.length>1 && $checkPermission('auth_business_order_check_order')"
                  @click="lookDetail(row)"
                  class="hover">{{lang.wait}}{{row.product_names.length}}{{lang.products}}</span>
                <span
                  v-if="row.product_names.length>1 && !$checkPermission('auth_business_order_check_order')">{{lang.wait}}{{row.product_names.length}}{{lang.products}}</span>
              </t-tooltip>
            </template>
            <template v-else>
              <!--  @click="itemClick(row)" -->
              <span class="aHover" @click="lookDetail(row)"
                v-if="$checkPermission('auth_business_order_check_order')">{{row.product_names[0]}}</span>
              <span v-else>{{row.product_names[0]}}</span>
              <span v-if="row.product_names.length>1 && $checkPermission('auth_business_order_check_order')"
                @click="lookDetail(row)" class="hover">{{lang.wait}}{{row.product_names.length}}{{lang.products}}</span>
              <span
                v-if="row.product_names.length>1 && !$checkPermission('auth_business_order_check_order')">{{lang.wait}}{{row.product_names.length}}{{lang.products}}</span>
            </template>
          </template>

          <span v-else class="child-name">
            <t-tooltip theme="light" :show-arrow="false" placement="top-right">
              <div slot="content" class="tool-content">{{row.description}}</div>
              <!-- <span @click="childItemClick(row)">{{row.product_name ? row.product_name : row.description}}
              <span class="host-name" v-if="row.host_name">({{row.host_name}})</span>
            </span> -->
              <a :href="row.host_id ? `host_detail.htm?client_id=${father_client_id}&id=${row.host_id}` : 'javascript:;'"
                class="aHover">{{row.product_name ? row.product_name : row.description}}
                <span class="host-name" v-if="row.host_name">({{row.host_name}})</span>
              </a>
            </t-tooltip>
          </span>
        </template>
        <template #order_amount="{row}">
          {{currency_prefix}}&nbsp;{{row.amount}}
          <!-- 升降机为退款时不显示周期 -->
          <span
            v-if="row.billing_cycle && Number(row.amount) >= 0 && row.type!=='upgrade'">/{{row.billing_cycle}}</span>
        </template>
        <template #order_status="{row}">
          <t-tag theme="default" variant="light" v-if="(row.status || row.host_status)==='Cancelled'"
            class="canceled order-canceled">{{lang.canceled}}
          </t-tag>
          <t-tag theme="default" variant="light" v-if="(row.status || row.host_status)==='Refunded'"
            class="canceled order-refunded">{{lang.refunded}}
          </t-tag>
          <t-tag theme="warning" variant="light" v-if="(row.status || row.host_status)==='Unpaid'"
            class="order-unpaid">{{lang.Unpaid}}
          </t-tag>
          <t-tag theme="primary" variant="light" v-if="row.status==='Paid'" class="order-paid">{{lang.Paid}}
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
          <t-tag theme="primary" variant="light" v-if="row.status ==='WaitUpload'">{{lang.order_wait_upload}}</t-tag>
          <t-tag theme="success" variant="light" v-if="row.status ==='WaitReview'">{{lang.order_wait_review}}</t-tag>
          <t-tag theme="danger" variant="light" v-if="row.status ==='ReviewFail'">{{lang.order_review_fail}}</t-tag>
        </template>
        <template #gateway="{row}">
          <template v-if="row.status === 'Unpaid'">
            --
          </template>
          <template v-else>
            <!-- 其他支付方式 -->
            <template v-if="row.gateway_sign === 'credit'">
              <!-- <span>{{lang.balance_pay}}</span> -->
              <span>{{row.gateway}}</span>
            </template>
            <!-- 混合支付 -->
            <template v-else>
              <template v-if="row.credit == 0">
                {{row.gateway}}
              </template>
              <template v-if="row.credit * 1 >0">
                <t-tooltip :content="currency_prefix+row.credit" theme="light" placement="bottom-right">
                  <span class="theme-color">{{lang.balance_pay}}</span>
                </t-tooltip>
                <span>{{row.gateway ? '+ ' + row.gateway: '' }}</span>
              </template>
            </template>
          </template>
        </template>
        <template #certification="{row}">
          <t-tooltip :show-arrow="false" theme="light">
            <span slot="content">{{!row.certification ? lang.real_tip8 : row.certification_type === 'person' ?
                      lang.real_tip9 : lang.real_tip10}}
            </span>
            <t-icon :class="row.certification ? 'green-icon' : ''"
              :name="!row.certification ? 'user-clear': row.certification_type === 'person' ? 'user' : 'usergroup'" />
          </t-tooltip>
        </template>
        <template #client_status="{row}">
          <t-tag theme="success" class="com-status" v-if="row.client_status" variant="light">{{lang.enable}}</t-tag>
          <t-tag theme="danger" class="com-status" v-else variant="light">{{lang.deactivate}}</t-tag>
        </template>
        <template #op="{row}">
          <template v-if="row.type">
            <!-- 审核 -->
            <t-tooltip :content="lang.order_audit" :show-arrow="false" theme="light"
              v-if="row.status === 'WaitReview' && $checkPermission('auth_business_order_review_voucher')">
              <svg class="common-look" @click="handleReview(row)">
                <use xlink:href="#icon-enable">
                </use>
              </svg>
            </t-tooltip>
            <!-- 上传/重新上传 -->
            <t-tooltip :content="row.status === 'WaitUpload' ? lang.upload_proof : lang.reupload" :show-arrow="false"
              theme="light" v-if="(row.status === 'WaitUpload' || row.status === 'WaitReview' || row.status === 'ReviewFail')
              && $checkPermission('auth_business_order_upload_voucher')">
              <t-icon name="upload" class="common-look" @click="handleUpload(row)"></t-icon>
            </t-tooltip>
            <!-- 查看凭证 -->
            <t-tooltip :content="lang.look_proof" :show-arrow="false" theme="light"
              v-if="row.status === 'Paid' && row.voucher.length > 0 && $checkPermission('auth_business_order_view_voucher')">
              <t-icon name="file" class="common-look" @click="lookProof(row)"></t-icon>
            </t-tooltip>
            <t-tooltip :content="`${lang.look}${lang.detail}`" :show-arrow="false" theme="light">
              <t-icon name="view-module" class="common-look" @click="lookDetail(row)"
                v-permission="'auth_business_order_check_order'"></t-icon>
            </t-tooltip>
            <t-tooltip :content="lang.update_price" :show-arrow="false" theme="light">
              <t-icon name="money-circle" class="common-look" @click="updatePrice(row, 'order')"
                v-if="row.status!=='Paid' && row.status!=='Cancelled' && row.status!=='Refunded' && $checkPermission('auth_business_order_adjust_order_amount')"></t-icon>
            </t-tooltip>
            <!-- <t-tooltip :content="lang.sign_pay" :show-arrow="false" theme="light" v-show="row.status!=='Paid' && row.status!=='Cancelled' && row.status!=='Refunded'">
            <t-icon name="discount" class="common-look" :class="{disable:row.status==='Paid'}" @click="signPay(row)"></t-icon>
          </t-tooltip> -->
            <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
              <t-icon name="delete" class="common-look" @click="delteOrder(row)"
                v-permission="'auth_business_order_delete_order'"></t-icon>
            </t-tooltip>
          </template>
          <template v-else>
            <t-tooltip :content="lang.edit" :show-arrow="false" theme="light"
              v-if="row.edit && $checkPermission('auth_business_order_adjust_order_amount')">
              <t-icon name="edit" size="18px" @click="updatePrice(row, 'sub')" class="common-look"></t-icon>
            </t-tooltip>
          </template>
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
    <!-- 标记支付 -->
    <t-dialog :header="lang.sign_pay" :visible.sync="payVisible" width="600" class="sign_pay">
      <template slot="body">
        <t-form :data="signForm">
          <t-form-item :label="lang.order_amount">
            <t-input :label="currency_prefix" v-model="signForm.amount" disabled />
          </t-form-item>
          <t-form-item :label="lang.balance_paid">
            <t-input :label="currency_prefix" v-model="signForm.credit" disabled />
          </t-form-item>
          <t-form-item :label="lang.no_paid">
            <t-input :label="currency_prefix" v-model="(signForm.amount * 1).toFixed(2)" disabled />
          </t-form-item>
          <t-checkbox v-model="use_credit" class="checkDelete">{{lang.use_credit}}</t-checkbox>
        </t-form>
      </template>
      <template slot="footer">
        <div class="common-dialog">
          <t-button @click="sureSign" :loading="submitLoading">{{lang.sure}}</t-button>
          <t-button theme="default" @click="payVisible=false">{{lang.cancel}}</t-button>
        </div>
      </template>
    </t-dialog>
    <!-- 调整价格 -->
    <t-dialog :header="lang.update_price" :visible.sync="priceModel" :footer="false">
      <t-form :data="formData" ref="update_price" @submit="onSubmit" :rules="rules" v-if="priceModel">
        <t-form-item :label="lang.change_money" name="amount">
          <t-input v-model="formData.amount" type="tel" :label="currency_prefix"
            :placeholder="lang.update_price_tip"></t-input>
        </t-form-item>
        <t-form-item :label="lang.description" name="description">
          <textarea class="t-textarea__inner" :placeholder="lang.description"
            v-model.lazy="formData.description"></textarea>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.sure}}</t-button>
          <t-button theme="default" variant="base" @click="priceModel=false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 删除 -->
    <t-dialog :header="deleteTit" :visible.sync="delVisible" class="delDialog" width="600">
      <template slot="body">
        <p class="tips">
          <t-icon name="error-circle" size="18"></t-icon>
          &nbsp;&nbsp;{{lang.sureDelete}}
        </p>
        <div class="check">
          <t-checkbox v-model="delete_host"></t-checkbox>
          <div class="tips">
            <p class="tit">{{lang.deleteOrderTip1}}<span class="com-red">{{lang.deleteOrderTip3}}</span></p>
            <p class="tip">({{lang.deleteOrderTip2}})</p>
          </div>
        </div>
      </template>
      <template slot="footer">
        <t-button @click="onConfirm" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
    <!-- 开启回收站 -->
    <t-dialog :header="lang.open_recycle_bin" :visible.sync="recycleVisble" :footer="false" class="recycle-dialog">
      <div class="con">
        <div class="icon success">
          <svg>
            <use :xlink:href="`${rootRul}img/icon/icons.svg#cus-recycle`">
            </use>
          </svg>
        </div>
        <div class="text">
          <p class="tit">{{lang.recycle_tip1}}</p>
          <p class="des">{{lang.recycle_tip2}}</p>
        </div>
      </div>
      <div class="com-f-btn">
        <t-button theme="primary" :loading="submitLoading" @click="handleRecyle">{{lang.sure}}</t-button>
        <t-button theme="default" variant="base" @click="recycleVisble=false">{{lang.cancel}}</t-button>
      </div>
    </t-dialog>

    <!-- 审核 -->
    <t-dialog :header="proofTitle" :visible.sync="reviewDialog" :footer="false" width="500" class="review-dialog">
      <div class="con">
        <div class="preview-list">
          <!-- <t-image-viewer v-for="(img, index) in imgList" :key="img" :default-index="index" :images="imgList">
            <template #trigger="{ open }">
              <div class="tdesign-demo-image-viewer__ui-image tdesign-demo-image-viewer__base">
                <span @click="open" class="name">{{curItem.voucher[index].name}}</span>
              </div>
            </template>
          </t-image-viewer> -->
          <p class="name" v-for="(item, index) in curItem.voucher" :key="index" @click="clickFile(item)">
            {{item.name}}
          </p>
        </div>
        <template v-if="!isLook">
          <t-form :data="reviewForm" @submit="submitReview" ref="proof" :rules="rules" reset-type="initial">
            <t-form-item name="pass" :required-mark="false">
              <t-select v-model="reviewForm.pass" @change="changeProofType">
                <t-option :value="0" :label="lang.no_pass" :key="0"></t-option>
                <t-option :value="1" :label="lang.pass" :key="1"></t-option>
              </t-select>
            </t-form-item>
            <t-form-item name="review_fail_reason" :required-mark="false" v-if="reviewForm.pass === 0">
              <t-textarea v-model="reviewForm.review_fail_reason" :placeholder="lang.order_fail_reason"></t-textarea>
            </t-form-item>
            <t-form-item name="transaction_number" :required-mark="false" v-else>
              <t-input v-model="reviewForm.transaction_number" :placeholder="lang.flow_number"></t-input>
            </t-form-item>
            <div class="com-f-btn">
              <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.sure}}</t-button>
              <t-button theme="default" variant="base" @click="reviewDialog=false">{{lang.cancel}}</t-button>
            </div>
        </template>
        </t-form>
      </div>
    </t-dialog>
    <!-- 上传/查看凭证 -->
    <t-dialog :header="proofTitle" :visible.sync="proofDialog" :footer="false" width="500" class="review-dialog">
      <div class="con">
        <t-form :data="proofForm" @submit="submitProof">
          <t-form-item name="voucher" :required-mark="false">
            <t-upload theme="custom" multiple v-model="proofForm.voucher" :action="uploadUrl"
              :before-upload="beforeUpload" :format-response="formatResponse" :headers="uploadHeaders" :max="10"
              accept="image/*, .pdf, .PDF">
              <t-button>{{lang.click_upload}}</t-button>
            </t-upload>
          </t-form-item>
          <p class="upload-btn">{{lang.order_upload_tip}}</p>
          <div class="preview-list">
            <!-- <t-image-viewer v-for="(item, index) in proofForm.voucher" :key="item.url" :default-index="index" :images="calcImageList">
              <template #trigger="{ open }">
                <span @click="open" class="name">
                  {{item.name}}
                  <span @click="delItem(index)">
                    <t-icon name="close"></t-icon>
                  </span>
                </span>
              </template>
            </t-image-viewer> -->
            <p class="name" v-for="(item, index) in proofForm.voucher" :key="index" @click="clickFile(item)">
              {{item.name}}
              <span @click="delItem(index)">
                <t-icon name="close"></t-icon>
              </span>
            </p>
          </div>
          <div class="com-f-btn">
            <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.sure}}</t-button>
            <t-button theme="default" variant="base" @click="proofDialog=false">{{lang.cancel}}</t-button>
          </div>
        </t-form>
      </div>
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

    <!-- 图片预览 -->
    <div style="height: 0;">
      <img id="viewer" :src="preImg" alt="">
    </div>
    <safe-confirm ref="safeRef" :password.sync="admin_operate_password" @confirm="hadelSafeConfirm"></safe-confirm>
  </com-config>
</div>
<script src="/{$template_catalog}/template/{$themes}/components/comViewFiled/comViewFiled.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/comTreeSelect/comTreeSelect.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/safeConfirm/safeConfirm.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/loginByUser/loginByUser.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/common/viewer.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/order.js"></script>
{include file="footer"}

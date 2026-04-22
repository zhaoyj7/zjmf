{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/upstream_order.css">
<div id="content" class="upstream_goods" v-cloak>
  <com-config>
    <t-card class="list-card-container b-table">
      <div class="common-header">
        <div class="flex">
          <t-button @click="addGroup" v-permission="'auth_upstream_downstream_upstream_product_create_group'">
            {{lang.upstream_text43}}</t-button>
          <t-button @click="addProduct"
            v-permission="'auth_upstream_downstream_upstream_product_create_upstream_product'">
            {{lang.add_other_product}}
          </t-button>
          <!-- 亏本交易拦截 -->
          <t-button @click="handleLossTradeIntercept">
            {{lang.loss_trade_intercept}}
          </t-button>
          <t-button @click="batchDelete" theme="danger"
            v-permission="'auth_upstream_downstream_upstream_product_delete_upstream_product'">
            {{lang.batch_delete}}
          </t-button>
        </div>
        <div class="client-search">
          <t-select v-model="params.supplier_id" :placeholder="lang.upstream_text141" clearable @change="search">
            <t-option v-for="item in supplierOption" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
          <t-select v-model="params.mode" :placeholder="lang.upstream_text142" clearable @change="search">
            <t-option value="sync" :label="lang.upstream_text103" key="sync"></t-option>
            <t-option value="only_api" :label="lang.upstream_text102" key="only_api"></t-option>
          </t-select>
          <t-select v-model="params.need_manual_sync" :placeholder="lang.upstream_text143" clearable @change="search">
            <t-option :value="0" :label="lang.manual_no" :key="0"></t-option>
            <t-option :value="1" :label="lang.manual_yes" :key="1"></t-option>
          </t-select>
          <t-input v-model="params.keywords" :placeholder="lang.upstream_text44" @keypress.enter.native="search"
            @clear="clearKey" clearable>
          </t-input>
          <t-button @click="search" class="search" class="com-gap">{{lang.query}}</t-button>
        </div>
      </div>
      <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading"
        :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width" @select-change="rehandleSelectChange"
        :selected-row-keys="checkId">
        <template #name="{row}">
          <a :href="`product_detail.htm?id=${row.id}`" target="_blank" class="common-look aHover"
            v-if="row.mode === 'sync' && row.profit_type === 1">
            {{row.name}}({{row.supplier_name}})
          </a>
          <span v-else>{{row.name}}({{row.supplier_name}})</span>
        </template>
        <template #group="{row}">
          <span>{{row.product_group_name_first}} - {{row.product_group_name_second}}</span>
        </template>
        <template #mode="{row}">
          {{row.mode === 'only_api' ? lang.upstream_text102 : lang.upstream_text103}}
        </template>
        <template #plan="{row}">
          <template v-if="row.mode === 'only_api'">
            <t-tooltip :show-arrow="false" theme="light">
              <div slot="content" class="profit-tip">
                <p>
                  {{lang.upstream_text84}}：{{row.profit_percent}}<span>{{row.profit_type === 0 ? '%' : currency_suffix}}</span>
                </p>
                <p>
                  {{lang.upstream_text85}}：{{row.renew_profit_percent}}<span>{{row.renew_profit_type === 0 ? '%' : currency_suffix}}</span>
                </p>
                <p>
                  {{lang.upstream_text86}}：{{row.upgrade_profit_percent}}<span>{{row.upgrade_profit_type === 0 ? '%' : currency_suffix}}</span>
                </p>
              </div>
              <span class="profit-txt">{{lang.upstream_text73}}</span>
            </t-tooltip>
          </template>
          <template v-else>
            <span v-if="row.profit_type === 0">{{row.profit_percent}}%</span>
            <span v-else>{{lang.upstream_text108}}</span>
          </template>
        </template>
        <!-- <template #profit_percent="{row}">
          <template v-if="row.mode === 'sync'">
            --
          </template>
          <template v-else>
            <span v-if="row.profit_type === 0">{{row.profit_percent}}%</span>
            <span v-if="row.profit_type === 1">{{currency_prefix}}{{row.profit_percent}}</span>
          </template>
        </template>
        <template #renew_profit_type="{row}">
          <template v-if="row.mode === 'sync'">
            --
          </template>
          <template v-else>
            <span v-if="row.renew_profit_type === 0">{{row.renew_profit_percent}}%</span>
            <span v-if="row.renew_profit_type === 1">{{currency_prefix}}{{row.renew_profit_percent}}</span>
          </template>
        </template>
        <template #upgrade_profit_percent="{row}">
          <template v-if="row.mode === 'sync'">
            --
          </template>
          <template v-else>
            <span v-if="row.upgrade_profit_type === 0">{{row.upgrade_profit_percent}}%</span>
            <span v-if="row.upgrade_profit_type === 1">{{currency_prefix}}{{row.upgrade_profit_percent}}</span>
          </template>
        </template> -->
        <template #price="{row}">
          <span>{{currency_prefix}}{{row.price}}<span v-if="row.cycle">/{{row.cycle}}</span> {{lang.rise}}</span>
        </template>
        <template #upstream_price="{row}">
          <span>{{currency_prefix}}{{row.upstream_price}}</span>
        </template>
        <template #hidden="{row}">
          <t-switch size="large" :custom-value="[0,1]" v-model="row.hidden" @change="onChange(row)"
            :disabled="!$checkPermission('auth_upstream_downstream_upstream_product_show_hide')"></t-switch>
        </template>
        <template #op="{row}">
          <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
            <t-icon name="edit" size="18px" class="common-look" @click="editGoods(row)"
              v-permission="'auth_upstream_downstream_upstream_product_update_upstream_product'"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.upstream_text140" :show-arrow="false" theme="light">
            <t-icon name="refresh" size="18px" class="common-look" @click="showSyncConfirm(row)" 
              v-if="row.need_manual_sync === 1 && $checkPermission('auth_upstream_downstream_upstream_product_manual_sync_resource')">
            </t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light" v-if="row.host_num === 0">
            <t-icon name="delete" size="18px" @click="deleteHandler(row.id)" class="common-look"
              v-permission="'auth_upstream_downstream_upstream_product_delete_upstream_product'"></t-icon>
          </t-tooltip>
        </template>
      </t-table>
      <com-pagination v-if="total" :total="total" :page="params.page" :limit="params.limit" @page-change="changePage">
      </com-pagination>
    </t-card>
    <!-- 新建分组 -->
    <t-dialog :header="lang.create_group" :visible.sync="groupModel" :footer="false" @close="closeGroup">
      <t-form :data="formData" ref="groupForm" @submit="onSubmit" :rules="rules" reset-type="initial">
        <t-form-item :label="lang.group_name" name="name">
          <t-input v-model="formData.name" :placeholder="lang.group_name"></t-input>
        </t-form-item>
        <t-form-item :label="lang.belong_group" name="id">
          <t-select v-model="formData.id" :placeholder="lang.belong_group" clearable>
            <t-option v-for="item in firstGroup" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="closeGroup">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 编辑商品 -->
    <t-dialog :header="isEdit ? lang.edit_goods : lang.add_other_product" :width="isEn ? '750px' : '620px'"
      :visible.sync="productModel" :footer="false" @close="closeProduct" class="pro-dialog"
      placement="center">
      <t-form :data="productData" ref="productForm" @submit="submitProduct" :rules="productRules" reset-type="initial">
        <t-form-item :label="lang.upstream_text6" name="supplier_id" :help="supplierTip" class="supplier-select">
          <t-select v-model="productData.supplier_id" :placeholder="lang.upstream_text6" filterable
            :popup-props="{ overlayInnerStyle: { height: '200px' } }"
            @change="supplierChange" :disabled="isEdit">
            <t-option v-for="item in supplierOption" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <!-- 添加类型，新增的时候才有，默认单商品 -->
        <t-form-item :label="lang.upstream_text131" class="required" v-if="!isEdit">
          <t-radio-group v-model="addType" @change="changeAddType">
            <t-radio :value="0">{{lang.upstream_text132}}</t-radio>
            <t-radio :value="1">{{lang.upstream_text133}}</t-radio>
          </t-radio-group>
        </t-form-item>
        <!-- :scroll="{ type: 'virtual' }" -->
        <t-form-item :label="lang.product" name="upstream_product_id">
          <t-select v-model="productData.upstream_product_id" :placeholder="lang.product" filterable
            :multiple="addType === 0 ? false : true" :popup-props="{ overlayInnerStyle: { height: '200px' } }"
            :disabled="isEdit" @change="chooseProduct" :min-collapsed-num="1">
            <t-option v-for="item in goodsOption" :value="item.id" :label="`#${item.id}-${item.name}`" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.product_name" name="name" v-if="addType === 0">
          <t-input v-model="productData.name" :placeholder="lang.product_name"></t-input>
        </t-form-item>
        <!-- 类型为default 并且是专业|企业版 -->
        <t-form-item :label="lang.upstream_text101" name="mode" v-if="calcType === 'default' || calcType === 'whmcs'">
          <t-radio-group v-model="productData.mode" @change="changeMode" :disabled="isEdit">
            <t-radio value="only_api">{{lang.upstream_text102}}</t-radio>
            <t-radio value="sync" v-show="isShowSync" :disabled="isEdit || edition !== 1">
              {{lang.upstream_text103}}
              <t-tooltip :content="lang.upstream_text104 + '\n' + lang.upstream_text105  + '\n' + lang.upstream_text106"
                :show-arrow="false" theme="light" placement="top-left" overlay-class-name="pre-wrap">
                <t-icon name="help-circle" size="16px"></t-icon>
              </t-tooltip>
            </t-radio>
          </t-radio-group>
        </t-form-item>
        <!-- 以本地商品代理 -->
        <template v-if="productData.mode === 'sync'">
          <t-form-item :label="lang.upstream_text107" class="required">
            <t-form-item name="profit_type"
              :help="productData.profit_type === 0 ? lang.upstream_text114 : lang.upstream_text115">
              <t-radio-group v-model="productData.profit_type" @change="changeWay('profit_percent')">
                <t-radio :value="0">{{lang.percent}}</t-radio>
                <t-radio :value="1">{{lang.upstream_text108}}</t-radio>
              </t-radio-group>
            </t-form-item>
            <t-form-item name="profit_percent" class="profit-input"
              :rules="[{required: true,message: lang.input, type: 'error'}]" v-show="productData.profit_type === 0"
              help=" ">
              <t-input-number v-model="productData.profit_percent" theme="normal" :decimal-places="2"
                :placeholder="lang.input" suffix="%"></t-input-number>
              <t-tooltip :content="lang.upstream_text113" :show-arrow="false" theme="light">
                <t-icon name="help-circle" size="18px"
                  style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
              </t-tooltip>
            </t-form-item>
          </t-form-item>
        </template>
        <!-- 以接口代理 -->
        <template v-else>
          <t-form-item :label="lang.upstream_text134" class="required">
            <t-radio-group v-model="productData.price_basis">
              <t-radio value="standard">{{lang.upstream_text135}}
                <t-tooltip :content="lang.upstream_text137" :show-arrow="false" theme="light">
                  <t-icon name="help-circle" size="18px"
                    style="color: var(--td-brand-color); cursor: pointer; margin-left: 5px;"></t-icon>
                </t-tooltip>
              </t-radio>
              <t-radio value="agent">{{lang.upstream_text136}}
                <t-tooltip :content="lang.upstream_text138" :show-arrow="false" theme="light">
                  <t-icon name="help-circle" size="18px"
                    style="color: var(--td-brand-color); cursor: pointer; margin-left: 5px;"></t-icon>
                </t-tooltip>
              </t-radio>
            </t-radio-group>
          </t-form-item>
          <!-- 利润方式改为三种：新购，续费，升级 -->
          <t-form-item :label="lang.upstream_text84" class="required">
            <t-form-item name="profit_type">
              <t-radio-group v-model="productData.profit_type" @change="changeWay('profit_percent')">
                <t-radio :value="0">{{lang.percent}}</t-radio>
                <t-radio :value="1">{{lang.fixed}}{{lang.upstream_text73}}</t-radio>
              </t-radio-group>
            </t-form-item>
            <t-form-item name="profit_percent" class="profit-input" v-if="productData.profit_type === 0">
              <t-input-number v-model="productData.profit_percent" theme="normal" :decimal-places="2"
                :placeholder="lang.input" suffix="%">
              </t-input-number>
              <t-tooltip :content="lang.upstream_text20" :show-arrow="false" theme="light">
                <t-icon name="help-circle" size="18px"
                  style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;">
                </t-icon>
              </t-tooltip>
            </t-form-item>
            <t-form-item name="profit_percent" v-else class="profit-input">
              <t-input-number v-model="productData.profit_percent" theme="normal" :decimal-places="2"
                :placeholder="lang.input">
              </t-input-number>
            </t-form-item>
          </t-form-item>
          <!--  续费 -->
          <t-form-item :label="lang.upstream_text85" class="required">
            <t-form-item name="renew_profit_type">
              <t-radio-group v-model="productData.renew_profit_type" @change="changeWay('renew_profit_percent')">
                <t-radio :value="0">{{lang.percent}}</t-radio>
                <t-radio :value="1">{{lang.fixed}}{{lang.upstream_text73}}</t-radio>
              </t-radio-group>
            </t-form-item>
            <t-form-item name="renew_profit_percent" v-if="productData.renew_profit_type === 0" class="profit-input">
              <t-input-number v-model="productData.renew_profit_percent" :placeholder="lang.input" theme="normal"
                :decimal-places="2" suffix="%"></t-input-number>
              <t-tooltip :content="lang.upstream_text20" :show-arrow="false" theme="light">
                <t-icon name="help-circle" size="18px"
                  style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
              </t-tooltip>
            </t-form-item>
            <t-form-item name="renew_profit_percent" v-else class="profit-input">
              <t-input-number v-model="productData.renew_profit_percent" :placeholder="lang.input" theme="normal"
                :decimal-places="2"></t-input-number>
            </t-form-item>
          </t-form-item>
          <!-- 升级 -->
          <t-form-item :label="lang.upstream_text86" class="required">
            <t-form-item name="upgrade_profit_type">
              <t-radio-group v-model="productData.upgrade_profit_type" @change="changeWay('upgrade_profit_percent')">
                <t-radio :value="0">{{lang.percent}}</t-radio>
                <t-radio :value="1">{{lang.fixed}}{{lang.upstream_text73}}</t-radio>
              </t-radio-group>
            </t-form-item>
            <t-form-item name="upgrade_profit_percent" v-if="productData.upgrade_profit_type === 0"
              class="profit-input">
              <t-input-number v-model="productData.upgrade_profit_percent" :placeholder="lang.input" suffix="%"
                theme="normal" :decimal-places="2"></t-input-number>
              <t-tooltip :content="lang.upstream_text20" :show-arrow="false" theme="light">
                <t-icon name="help-circle" size="18px"
                  style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
              </t-tooltip>
            </t-form-item>
            <t-form-item name="upgrade_profit_percent" v-else class="profit-input">
              <t-input-number v-model="productData.upgrade_profit_percent" :placeholder="lang.input" theme="normal"
                :decimal-places="2"></t-input-number>
            </t-form-item>
          </t-form-item>
          <t-form-item label=" " class="empty-item">
            <span class="tip">{{lang.upstream_text87}}</span>
          </t-form-item>
        </template>

        <t-form-item :label="lang.upstream_text21" name="dec" key="goods_description">
          <com-tinymce ref="comTinymce" id="goods_description" :default-value="productData.description"
            :pre-placeholder="lang.upstream_text22">
          </com-tinymce>
        </t-form-item>
        <t-form-item :label="lang.upstream_text23" name="auto_setup">
          <t-switch size="large" :custom-value="[1,0]" v-model="productData.auto_setup"></t-switch>
        </t-form-item>
        <t-form-item :label="lang.upstream_text24" name="certification" class="no-flex">
          <t-switch size="large" :custom-value="[1,0]" v-model="productData.certification"></t-switch>
          <t-tooltip :content="lang.upstream_text25" :show-arrow="false" theme="light">
            <t-icon name="help-circle" size="18px"
              style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
          </t-tooltip>
          <div class="tips-div">{{lang.upstream_text26}}</div>
        </t-form-item>
        <t-form-item :label="lang.upstream_text46" name="sync" v-if="calcType === 'finance'" class="no-flex">
          <t-tooltip :content="lang.upstream_text47" :show-arrow="false" theme="light">
            <t-switch size="large" :custom-value="[1,0]" v-model="productData.sync" disabled></t-switch>
          </t-tooltip>
          <div class="tips-div">{{lang.upstream_text48}}</div>
        </t-form-item>
        <!-- <t-form-item label="上游实名方式" name="certification_method">
        <t-select v-model="productData.certification_method" placeholder="上游实名方式">
          <t-option v-for="item in methodOption" :value="item.id" :label="item.name" :key="item.id">
          </t-option>
        </t-select>
      </t-form-item> -->
        <t-form-item :label="lang.first_group" name="firstId">
          <t-select v-model="productData.firstId" :placeholder="lang.group_name" @change="changeFirId">
            <t-option v-for="item in firstGroup" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.second_group" name="product_group_id">
          <t-select v-model="productData.product_group_id" :placeholder="lang.group_name">
            <t-option v-for="item in tempSecondGroup" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="closeProduct">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 删除弹窗 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureDel" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
    <!-- 亏本交易拦截 -->
    <t-dialog :header="lang.loss_trade_intercept" :visible.sync="lossConfigDialog" :footer="false"
      @close="lossConfigDialog = false" width="550" class="loss-dialog">
      <t-form :data="lossConfig" ref="groupForm" label-align="left" @submit="onSubmitLossConfig" :rules="rules"
        reset-type="initial">
        <t-form-item :label="lang.loss_new">
          <t-checkbox v-model="lossConfig.upstream_intercept_new_order_notify">{{lang.loss_notice_admin}}</t-checkbox>
          <t-checkbox v-model="lossConfig.upstream_intercept_new_order_reject">{{lang.loss_reject_order}}</t-checkbox>
        </t-form-item>
        <t-form-item :label="lang.renew">
          <t-checkbox v-model="lossConfig.upstream_intercept_renew_notify">{{lang.loss_notice_admin}}</t-checkbox>
          <t-checkbox v-model="lossConfig.upstream_intercept_renew_reject">{{lang.loss_reject_order}}</t-checkbox>
        </t-form-item>
        <t-form-item :label="lang.host_upgrade">
          <t-checkbox v-model="lossConfig.upstream_intercept_upgrade_notify">{{lang.loss_notice_admin}}</t-checkbox>
          <t-checkbox v-model="lossConfig.upstream_intercept_upgrade_reject">{{lang.loss_reject_order}}</t-checkbox>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="lossConfigDialog = false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 同步确认弹窗 -->
    <t-dialog theme="warning" :header="lang.upstream_text139" :visible.sync="syncConfirmVisible">
      <template slot="footer">
        <t-button theme="primary" @click="confirmSync" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="syncConfirmVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/tinymce/tinymce.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/comTinymce/comTinymce.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/upstream.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/upstream_goods.js"></script>
{include file="footer"}

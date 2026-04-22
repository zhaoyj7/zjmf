{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/product.css">
<div id="content" class="product-detail hasCrumb" v-cloak>
  <com-config>
    <!-- crumb -->
    <div class="com-crumb">
      <span>{{lang.refund_commodit_management}}</span>
      <t-icon name="chevron-right"></t-icon>
      <a href="product.htm">{{lang.product_list}}</a>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.basic_info}}</span>
    </div>
    <t-card class="product-container com-sidebar-box">
      <div class="left-box">
        <ul class="common-tab">
          <li class="all">{{lang.auth_all}}</li>
          <li class="active" v-permission="'auth_product_detail_basic_info_view'">
            <a>{{lang.basic_info}}</a>
          </li>
          <li v-permission="'auth_product_detail_server_view'">
            <a :href="`product_api.htm?id=${id}`">{{lang.interface_manage}}</a>
          </li>
          <li v-permission="'auth_product_detail_custom_field_view'">
            <a :href="`product_self_field.htm?id=${id}`">{{lang.product_selef_text1}}</a>
          </li>
          <li v-permission="'auth_product_detail_custom_host_name'">
            <a :href="`product_custom_name.htm?id=${id}`">{{lang.product_custom_name}}</a>
          </li>
        </ul>
      </div>
      <div class="box">
        <t-form :data="formData" :rules="rules" ref="userInfo">
          <t-row :gutter="{ xs: 0, sm: 20, md: 40, lg: 60, xl: 60, xxl: 60 }">
            <!-- 个人中心左侧 -->
            <t-col :xs="12" :xl="6">
              <p class="com-tit"><span>{{ lang.basic_info }}</span></p>
              <div class="item">
                <t-form-item :label="lang.product_name" name="name">
                  <t-input v-model="formData.name" :placeholder="`${lang.product_name}${multiliTip}`"></t-input>
                </t-form-item>
                <t-form-item :label="lang.product_group" name="product_group_id">
                  <!-- <t-select v-model="formData.product_group_id" :popup-props="popupProps" :placeholder="lang.group">
                    <t-option v-for="item in secondGroup" :value="item.id" :label="item.name" :key="item.id">
                    </t-option>
                  </t-select> -->
                  <com-tree-select :value="formData.product_group_id" @choosepro="choosePro" :is-only-group="true">
                  </com-tree-select>
                </t-form-item>
              </div>
              <div class="item">
                <t-form-item name="qty">
                  <template slot="label">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                      <div style="display: flex; align-items: center;">
                        <span style="margin-right: 5px;">{{lang.inventory}}</span>
                        <t-switch size="medium" :custom-value="[1,0]" v-model="formData.stock_control"></t-switch>
                      </div>
                      <div style="display: flex; align-items: center;">
                        <span style="margin-right: 5px;">{{lang.inventory_rollback}}</span>
                        <t-switch size="medium" :custom-value="[1,0]" v-model="formData.sync_stock"></t-switch>
                      </div>
                    </div>
                  </template>
                  <t-input v-model="formData.qty" :placeholder="lang.inventory_placeholder"></t-input>
                </t-form-item>
                <t-form-item :label="lang.hidden" name="hidden">
                  <t-select v-model="formData.hidden">
                    <t-option :value="1" :label="lang.open" :key="1"></t-option>
                    <t-option :value="0" :label="lang.close" :key="0"></t-option>
                  </t-select>
                </t-form-item>
              </div>
              <div class="item">
                <t-form-item style="margin-bottom: 24px;">
                  <template slot="label">
                    {{lang.start_price}}
                    <t-tooltip :content="lang.start_price_tip" :show-arrow="false" theme="light" placement="top-left">
                      <t-icon name="help-circle" class="pack-tip"></t-icon>
                    </t-tooltip>
                  </template>
                  <t-input-number v-model="formData.price" theme="normal" :min="0" :decimal-places="2"
                    :placeholder="lang.start_price">
                  </t-input-number>
                </t-form-item>
                <t-form-item class="agent-item" v-if="formData.mode === 'sync'">
                  <div slot="label" class="cus-label">
                    <span>{{lang.upstream_text109}}</span>
                    <t-button size="small" :loading="syncLoading" @click="handleSync" v-if="formData.profit_type === 1">
                      {{lang.upstream_text110}}
                      <t-tooltip :content="lang.upstream_text111 + '\n' + lang.upstream_text112" :show-arrow="false"
                        theme="light" placement="top-left" overlay-class-name='custom-name-pup'>
                        <t-icon name="help-circle"></t-icon>
                      </t-tooltip>
                    </t-button>
                  </div>
                  <t-input v-model="formData.supplier_name" disabled>
                  </t-input>
                </t-form-item>
              </div>
              <t-form-item name="description" class="textarea">
                <template slot="label">
                  {{lang.product_descript}}
                  <t-tooltip :content="lang.product_descript + multiliTip" :show-arrow="false" theme="light"
                    placement="top-left">
                    <t-icon name="help-circle" class="pack-tip"></t-icon>
                  </t-tooltip>
                </template>
                <!-- <t-textarea :placeholder="`${lang.product_descript}${multiliTip}`" v-model="formData.description" /> -->
                <com-tinymce ref="comTinymce" id="cart_instruction" :default-value="formData.description"
                  :pre-placeholder="`${lang.product_descript}${multiliTip}`">
                </com-tinymce>
              </t-form-item>
            </t-col>
            <!-- 个人中心右侧 -->
            <t-col :xs="12" :xl="6" class="r-box">
              <p class="com-tit free"><span>{{lang.cost}}</span></p>
              <!-- 费用类型 -->
              <t-row :gutter="{ xs: 0, xxl: 30 }" class="dis-box">
                <t-col :xs="12" :xl="6">
                  <p>{{lang.cost_type}}</p>
                  <t-popconfirm :visible="visibleFree" theme="warning" @confirm="confirmChange"
                    @cancel="visibleFree = false" :content="lang.free_type_tip + '\n' + lang.free_type_tip1">
                    <t-select :value="formData.pay_type" @change="changeFreeType" :disabled="formData.mode === 'sync'">
                      <t-option v-for="item in payType" :value="item.value" :label="item.label" :key="item.value">
                      </t-option>
                    </t-select>
                  </t-popconfirm>
                </t-col>
              </t-row>
              <p class="com-tit connect"><span>{{ lang.upAndDown }}</span></p>
              <div class="item">
                <t-form-item :label="lang.demote_range" name="language">
                  <t-select v-model="formData.upgrade" multiple :min-collapsed-num="1" :popup-props="popupProps"
                    :disabled="formData.mode === 'sync'">
                    <t-option v-for="item in relationList" :value="item.id" :label="item.name" :key="item.id">
                    </t-option>
                  </t-select>
                </t-form-item>
              </div>
              <!-- 到期日计算规则 -->
              <p class="com-tit connect"><span>{{ lang.product_due_rule }}</span></p>
              <div class="item rule">
                <t-form-item>
                  <t-radio-group v-model="formData.renew_rule" :disabled="formData.mode === 'sync'">
                    <t-radio value="current">{{lang.product_due_reality}}<span
                        class="des">{{lang.product_due_tip1}}</span></t-radio>
                    <t-radio value="due">{{lang.product_due_cycle}}<span
                        class="des">{{lang.product_due_tip2}}</span></t-radio>
                  </t-radio-group>
                </t-form-item>
              </div>
              <!-- 自动续费时间 -->
              <p class="com-tit connect"><span>{{ lang.product_due_auto }}</span></p>
              <t-form-item>
                <div style="width: 100%;">
                  <div style="display: flex; align-items: center; width: 100%;">
                    <span style="flex-shrink: 0;">{{lang.product_due_tip3}}</span>
                    <t-switch size="medium" :custom-value="[1,0]" style="margin:0 10px;"
                      v-model="formData.auto_renew_in_advance"></t-switch>
                    <template v-if="formData.auto_renew_in_advance == 1">
                      <t-input-number theme="normal" :min="0" :decimal-places="0" style="width: 130px;"
                        v-model="formData.auto_renew_in_advance_num">
                      </t-input-number>
                      <t-select v-model="formData.auto_renew_in_advance_unit" style="width: 80px;">
                        <t-option value="minute" :label="lang.debug_minutes"></t-option>
                        <t-option value="hour" :label="lang.hour"></t-option>
                        <t-option value="day" :label="lang.product_set_text34"></t-option>
                      </t-select>
                    </template>
                  </div>
                  <div style="color: #999; margin-top: 5px;">{{lang.product_due_tip6}}</div>
                </div>
              </t-form-item>
            </t-col>
          </t-row>
        </t-form>
        <!-- 底部操作按钮 -->
        <div class="footer-btn">
          <t-button theme="primary" @click="updateUserInfo" type="submit" :loading="submitLoading"
            v-permission="'auth_product_detail_basic_info_save_info'">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="back">{{lang.close}}</t-button>
        </div>
      </div>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/tinymce/tinymce.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/comTinymce/comTinymce.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/comTreeSelect/comTreeSelect.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/product_detail.js"></script>
{include file="footer"}

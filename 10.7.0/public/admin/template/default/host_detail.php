{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<script src="/{$template_catalog}/template/{$themes}/js/common/jquery.min.js"></script>
<!-- =======内容区域======= -->
<div id="content" class="host-detail hasCrumb" v-cloak>
  <com-config>
    <!-- crumb -->
    <div class="com-crumb">
      <span>{{lang.user_manage}}</span>
      <t-icon name="chevron-right" v-permission="'auth_user_list_view'"></t-icon>
      <span style="cursor: pointer;" @click="goClient" v-permission="'auth_user_list_view'">{{lang.user_list}}</span>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.product_info}}</span>
      <span class="back-text" @click="goBack">
        <t-icon name="chevron-left-double"></t-icon>{{lang.back}}
      </span>
    </div>
    <t-card class="list-card-container">
      <div class="com-h-box">
        <ul class="common-tab">
          <li v-permission="'auth_user_detail_personal_information_view'">
            <a :href="`${baseUrl}/client_detail.htm?id=${client_id}`">{{lang.personal}}</a>
          </li>
          <li class="active" v-permission="'auth_user_detail_host_info_view'">
            <a :href="`${baseUrl}/client_host.htm?id=${client_id}`">{{lang.product_info}}</a>
          </li>
          <li v-permission="'auth_user_detail_order_view'">
            <a :href="`${baseUrl}/client_order.htm?id=${client_id}`">{{lang.order_manage}}</a>
          </li>
          <li v-permission="'auth_user_detail_transaction_view'">
            <a :href="`${baseUrl}/client_transaction.htm?id=${client_id}`">{{lang.flow}}</a>
          </li>
          <li v-permission="'auth_user_detail_operation_log'">
            <a :href="`${baseUrl}/client_log.htm?id=${client_id}`">{{lang.operation}}{{lang.log}}</a>
          </li>
          <li
            v-if="$checkPermission('auth_user_detail_notification_log_sms_notification') || $checkPermission('auth_user_detail_notification_log_email_notification')">
            <a
              :href="`${baseUrl}/${($checkPermission('auth_user_detail_notification_log_sms_notification') ? 'client_notice_sms' : 'client_notice_email')}.htm?id=${client_id}`">{{lang.notice_log}}</a>
          </li>
          <li v-if="hasTicket && $checkPermission('auth_user_detail_ticket_view')">
            <a :href="`${baseUrl}/plugin/idcsmart_ticket/client_ticket.htm?id=${client_id}`">{{lang.auto_order}}</a>
          </li>
          <li v-permission="'auth_user_detail_info_record_view'">
            <a :href="`${baseUrl}/client_records.htm?id=${client_id}`">{{lang.info_records}}</a>
          </li>
        </ul>
        <div class="user">
          <!-- 顶部右侧选择用户 -->
          <com-choose-user :cur-info="clientDetail" :clearable="false" @changeuser="changeUser"
            class="com-clinet-choose">
          </com-choose-user>
          <t-select class="pro-select" v-model="formData.id" :placeholder="lang.tailorism" @change="changePro">
            <t-option v-for="item in hostArr" :value="item.id" :label="`#${item.id}-${item.product_name}`"
              :key="item.id"></t-option>
          </t-select>
          <t-button class="btn-transfer"
            v-if="hasTransfer && $checkPermission('auth_business_host_detail_host_transfer')" @click="handelTransfer">
            {{lang.host_transfer_text11}}
          </t-button>
        </div>

      </div>
      <div class="box">
        <t-form :data="formData" :rules="rules" ref="userInfo" label-align="top">
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 90 }">
            <t-col :xs="12" :xl="6">
              <p class="com-tit"><span>{{ lang.basic_info }}</span></p>
              <div class="item">
                <t-form-item :label="lang.product" name="product_id">
                  <!-- 商品选择： 根据插件设置的下拉类型处理 -->
                  <t-select v-model="formData.product_id" :popup-props="popupProps" v-if="selectWay === 'default'">
                    <t-option v-for="item in proList" :value="item.key" :key="item.key" :label="item.name">
                      <span :style="{color: item.hidden === 1 ? 'red' : ''}">{{ item.name }}</span>
                    </t-option>
                  </t-select>
                  <t-tree-select :data="calcProduct" v-else v-model="formData.product_id" :popup-props="popupProps"
                    :tree-props="treeProps" filterable clearable :placeholder="lang.tailorism">
                  </t-tree-select>
                </t-form-item>
                <t-form-item :label="lang.interface" v-if="!isAgent">
                  <t-select v-model="formData.server_id" :popup-props="popupProps">
                    <t-option v-for="item in serverList" :value="item.id" :label="item.name" :key="item.id">
                    </t-option>
                  </t-select>
                </t-form-item>
              </div>
              <div class="item">
                <t-form-item :label="lang.host_name" name="name">
                  <t-input v-model="formData.name" :placeholder="lang.host_name"></t-input>
                </t-form-item>
                <t-form-item :label="lang.status">
                  <t-select v-model="formData.status" :popup-props="popupProps">
                    <t-option v-for="item in status" :value="item.value" :label="item.label" :key="item.value">
                    </t-option>
                  </t-select>
                </t-form-item>
              </div>
              <t-form-item :label="lang.admin_notes" name="notes">
                <t-textarea v-model="formData.notes" :placeholder="lang.admin_notes"></t-textarea>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="6">
              <p class="com-tit"><span>{{ lang.setting_text38 }}</span></p>
              <p>{{lang.setting_text39}}</p>
              <!-- 1-31 后台返回操作按钮模块 -->
              <div class="module-opt" style="display: flex;flex-wrap: wrap;gap: 8px;">
                <div class="left">
                  <div v-for="(item,index) in optBtns" :key="index">
                    <t-button @click="handlerMoudle(item.func)" v-if="$checkPermission(item.auth)">
                      <template v-if="item.func !== 'terminate'">{{item.name}}</template>
                      <t-tooltip placement="top-right" :content="lang.module_tip" :show-arrow="false" theme="light"
                        v-else>
                        {{item.name}}
                        <t-icon name="help-circle" size="18px" />
                      </t-tooltip>
                    </t-button>
                  </div>
                  <!-- 暂时先固定添加： 重置授权 -->
                  <!-- <t-button @click="handleResetAuth">重置授权</t-button> -->
                </div>
                <div class="right">
                  <t-button @click="jumpToOrder">
                    {{lang.connect}}{{lang.order}}
                  </t-button>
                  <t-button @click="jumpToTicket"
                    v-if="(hasTicket || hasNewTicket) && $checkPermission('auth_user_detail_ticket_view')">
                    {{lang.connect}}{{lang.auto_order}}
                  </t-button>
                  <t-button @click="jumpToPro(connectProId)" v-if="connectProId">
                    {{lang.order_hosts}}
                  </t-button>
                </div>
              </div>
              <div class="operate-box" id="operateBox" v-permission="'auth_business_host_detail_module_operate'"></div>
              <!-- 流量包下单 -->
              <!-- <t-button v-if="hasFlow && useableFlowList.length" @click="handleFlow"
                style="margin-top: 20px;">{{lang.module_flow_place_order}}</t-button> -->
            </t-col>
            <t-col :xs="12" :xl="6" style="margin-top: 20px;">
              <p class="com-tit"><span>{{lang.financial_infos}}</span></p>
              <!-- 续费 -->
              <!-- <t-button theme="primary" class="renew-btn" @click="renewDialog" v-if="(curStatus === 'Active' || curStatus === 'Suspended') && hasPlugin && tempCycle !== 'free'  && tempCycle !== 'onetime'">{{lang.renew}}</t-button> -->
              <div class="config-item" style="grid-template-columns:1fr 1fr;">
                <template v-if="!isDemand">
                  <t-form-item :label="lang.buy_amount" name="first_payment_amount">
                    <t-input v-model="formData.first_payment_amount" :placeholder="lang.buy_amount">
                    </t-input>
                  </t-form-item>
                  <t-form-item :label="lang.billing_cycle">
                    <!-- <t-input v-model="formData.billing_cycle_name" disabled></t-input> -->
                    <!-- 2025-11-17 改成可下拉切换周期 -->
                    <t-select v-model="formData.billing_cycle_name">
                      <t-option v-for="item in formData.cycles" :value="item.billing_cycle" :label="item.name_show"
                        :key="item.id">
                    </t-select>
                  </t-form-item>
                  <!-- 续费方式 -->
                  <t-form-item name="renew_amount" :required-mark="false" class="renew-item">
                    <div slot="label" class="custom-label">
                      <span class="label">{{lang.cur_renew_price}}</span>
                      <div class="right" v-if="tempHostId === 0 || isSync">
                        {{lang.bt_tip4}}
                        <t-switch size="small" :custom-value="[1,0]" v-model="formData.ratio_renew"></t-switch>
                        <t-tooltip placement="top-right" :content="lang.bt_tip5" :show-arrow="false" theme="light">
                          <t-icon name="help-circle" size="18px" />
                        </t-tooltip>
                      </div>
                    </div>
                    <t-input v-model="formData.renew_amount" :placeholder="lang.renew_amount"></t-input>
                  </t-form-item>
                  <!-- 当前周期原价 -->
                  <t-form-item name="base_price" v-if="tempHostId === 0 || isSync" class="renew-item">
                    <div slot="label" class="custom-label">
                      <span class="label">{{lang.cur_cycle_price}}</span>
                      <div class="right">
                        {{lang.up_down_price_tip}}
                        <t-switch size="small" :custom-value="[1,0]" v-model="formData.upgrade_renew_cal"></t-switch>
                        <t-tooltip placement="top-right" :content="lang.cur_cycle_tip" :show-arrow="false"
                          theme="light">
                          <t-icon name="help-circle" size="18px" />
                        </t-tooltip>
                      </div>
                    </div>
                    <t-input v-model="formData.base_price"></t-input>
                  </t-form-item>
                </template>
                <!-- 按需 -->
                <template v-else>
                  <t-form-item :label="lang.demand_init_fee" name="first_payment_amount">
                    <t-input v-model="formData.first_payment_amount" :placeholder="lang.demand_init_fee">
                    </t-input>
                  </t-form-item>
                  <div class="demand-cycle"
                    :class="{half: formData.on_demand_billing_cycle_unit === 'day', three: formData.on_demand_billing_cycle_unit === 'month'}">
                    <t-form-item :label="lang.demand_pay_cycle">
                      <t-select v-model="formData.on_demand_billing_cycle_unit">
                        <t-option :value="item.value" :label="item.label" v-for="(item,index) in cycleUnitList"
                          :key="index"> </t-option>
                      </t-select>
                    </t-form-item>
                    <t-form-item name="on_demand_billing_cycle_day"
                      v-if="formData.on_demand_billing_cycle_unit === 'month'">
                      <t-select v-model="formData.on_demand_billing_cycle_day" :disabled="isAgent">
                        <t-option :value="item.value" :label="item.label" v-for="item in dayArr"
                          :key="item.value"></t-option>
                      </t-select>
                    </t-form-item>
                    <t-form-item name="on_demand_billing_cycle_point" v-if="formData.on_demand_billing_cycle_unit &&
                       formData.on_demand_billing_cycle_unit !== 'hour'">
                      <t-time-picker format="HH:mm" v-model="formData.on_demand_billing_cycle_point"
                        :disabled="isAgent"></t-time-picker>
                    </t-form-item>
                  </div>
                  <t-form-item :label="lang.demand_cycle_price">
                    <t-input-number v-model="formData.renew_amount" theme="normal" :min="0" :decimal-places="4"
                      :placeholder="lang.demand_cycle_price">
                    </t-input-number>
                  </t-form-item>
                  <t-form-item :label="lang.demand_flow">
                    <t-input-number theme="normal" :min="0" :decimal-places="4" :label="currency_prefix" suffix="/GB"
                      v-model="formData.on_demand_flow_price">
                    </t-input-number>
                  </t-form-item>
                  <t-form-item :label="lang.demand_keep_price">
                    <t-input-number theme="normal" :min="0" :label="currency_prefix" :decimal-places="4"
                      v-model="formData.keep_time_price">
                    </t-input-number>
                  </t-form-item>
                </template>
                <!-- 按需 end -->
                <t-form-item :label="lang.billing_way">
                  <t-select v-model="formData.billing_cycle" :popup-props="popupProps" :disabled="isDemand">
                    <t-option v-for="item in cycleList" :value="item.value" :label="item.label" :key="item.value">
                    </t-option>
                  </t-select>
                </t-form-item>
                <t-form-item :label="lang.open_time" name="active_time" :rules="[{ validator: checkTime}]">
                  <t-date-picker mode="date" allow-input format="YYYY-MM-DD HH:mm:ss" enable-time-picker
                    :presets="presets" v-model="formData.active_time" @change="changeActive" />
                </t-form-item>
                <t-form-item :label="lang.due_time" name="due_time" :rules="[{ validator: checkTime1}]"
                  v-if="!isDemand">
                  <t-date-picker mode="date" allow-input format="YYYY-MM-DD HH:mm:ss" enable-time-picker
                    :presets="presets" v-model="formData.due_time" @change="changeActive" :disabled="disabled" />
                </t-form-item>
                <t-form-item :label="lang.auto_renew" name="auto_renew"
                  v-if="formData.billing_cycle === 'recurring_prepayment'">
                  <t-switch :custom-value="[1,0]" v-model="formData.auto_renew"></t-switch>
                </t-form-item>
              </div>
            </t-col>
            <t-col :xs="12" :xl="6" style="margin-top: 20px;">
              <!-- 优惠码 -->
              <template v-if="hasPlugin">
                <p class="com-tit"><span>{{lang.promo_code}}</span></p>
                <t-table row-key="id" :data="promoList" size="medium" :columns="recordColumns" :hover="hover" bordered
                  :loading="recordLoading" :table-layout="tableLayout ? 'auto' : 'fixed'" :max-height="342">
                  <template #create_time="{row}">
                    {{row.create_time ? moment(row.create_time * 1000).format('YYYY/MM/DD HH:mm') : '--'}}
                  </template>
                  <template #scene="{row}">
                    {{lang[row.scene]}}
                  </template>
                  <template #order_id="{row}">
                    <a class="jump" @click="jumpOrder(row)">{{row.order_id}}</a>
                  </template>
                  <template #promo="{row}">
                    {{row.code}}：-{{currency_prefix}}{{row.discount}}
                  </template>
                </t-table>
              </template>
            </t-col>
            <t-col :xs="24" :xl="12" style="margin-top: 30px;" v-if="isAgent || hostFieldList.length > 0">
              <p class="com-tit"><span>{{lang.box_label22}}</span></p>
              <!-- 修改host id -->
              <div class="config-item" v-if="isAgent">
                <t-form-item :label="lang.upstream_host_id">
                  <t-input-number v-model="formData.upstream_host_id" :min="0" :decimal-places="0" theme="normal"
                    style="width: 100%;">
                  </t-input-number>
                </t-form-item>
                <t-form-item :label="lang.box_title47" class="agent-more-ip">
                  <t-input disabled v-model="ipDetails.dedicate_ip"></t-input>
                  <t-popup placement="top" trigger="hover">
                    <template #content>
                      <div class="ips">
                        <p v-for="(item,index) in allIp" :key="index">
                          {{item}}
                          <svg class="common-look" @click="copyIp(item)">
                            <use xlink:href="#icon-copy">
                            </use>
                          </svg>
                        </p>
                      </div>
                    </template>
                    <span v-if="ipDetails.ip_num > 1" class="showIp">
                      ({{ipDetails.ip_num}})
                      <svg class="common-look" @click="copyIp(allIp)">
                        <use xlink:href="#icon-copy">
                        </use>
                      </svg>
                    </span>
                  </t-popup>
                </t-form-item>

              </div>
              <!-- 渲染配置信息 -->
              <template v-for="(item,index) in hostFieldList">
                <p style="margin: 0; font-weight: bold;">{{item.name}}</p>
                <div class="config-item render">
                  <t-form-item :label="el.name" v-for="(el,ind) in item.field" :key="ind"
                    :class="{'disable-item': el.disable && el.key === 'zjmf_cloud_id'}">
                    <t-select v-model="el.value" v-if="el.options" :disabled="isSync">
                      <t-option :value="op.id + ''" :label="op.name" v-for="op in el.options" :key="op.id">
                      </t-option>
                    </t-select>
                    <template v-else>
                      <t-date-picker enable-time-picker allow-input v-model="el.value" format="YYYY-MM-DD hh:mm"
                        :presets="presets" v-if="el.type === 'date'" :disabled="isSync">
                      </t-date-picker>
                      <template v-else-if="el.key === 'password'">
                        <t-input type="password" v-model="el.value" autocomplete="off" :readonly="isSync">
                        </t-input>
                        <input class="com-empty-input"></input>
                      </template>
                      <t-input v-else-if="el.key === 'ip_num' && el?.sync_firewall_rule == 1" :disabled="isSync"
                        v-model=" el.value">
                        <template #suffix-icon>
                          <span style="color: var(--td-brand-color);cursor: pointer;"
                            @click="openDefence">{{lang.defence_detail}}</span>
                        </template>
                      </t-input>
                      <t-checkbox v-model="el.value" v-else-if="el.type === 'checkbox'" :disabled="isSync"></t-checkbox>
                      <template v-else>
                        <t-popup trigger="hover" :disabled="!el.num || el.num < 2" v-if="el.disable"
                          overlay-class-name="host-ips" placement="top-right">
                          <template #content>
                            <p v-for="(item,index) in calcAllIp(el.value)" :key="index">
                              {{item}}
                            </p>
                          </template>
                          <t-input :value="calcDisableValue(el)" :disabled="el.disable || isSync"></t-input>
                        </t-popup>
                        <t-input v-model="el.value" v-else :disabled="isSync"></t-input>
                      </template>

                      <span v-if="el.disable && el.key === 'zjmf_cloud_id'"
                        class="disable-tip">{{lang.manual_resource_tip}}
                      </span>
                    </template>
                    <!-- DCIM手动资源 -->
                    <t-button class="distribute" @click="handlerDistribute('dcim')" :disabled="isSync"
                      v-if="el.key === 'manual_resource' && $checkPermission('auth_business_host_detail_dcim_host_allot')">{{lang.distribute}}
                    </t-button>
                    <!-- 云手动资源 -->
                    <t-button class="distribute" @click="handlerDistribute('cloud')" :disabled="isSync"
                      v-if="el.key === 'cloud_manual_resource' && $checkPermission('auth_business_host_detail_dcim_host_allot')">{{lang.distribute}}
                    </t-button>
                    <!-- 机柜租用 -->
                    <template v-if="el.key === 'zjmf_dcim_cabinet_id'">
                      <t-button class="distribute" @click="handlerCabinet" v-if="curDcimId === '0'"
                        :disabled="isSync">{{lang.distribute}}
                      </t-button>
                      <t-button class="distribute" v-else @click="optItem('', 'cabinetFree')"
                        :disabled="isSync">{{lang.idle}}
                      </t-button>
                    </template>

                    <!-- DCIM机器 -->
                    <template
                      v-if="el.key === 'zjmf_dcim_id' && $checkPermission('auth_business_host_detail_dcim_host_allot')">
                      <!-- 分配  v-if="curDcimId === '0'"-->
                      <t-button class="distribute" @click="showDcim(el.server_group_id)" :disabled="isSync">
                        {{lang.distribute}}
                      </t-button>
                      <template v-if="curDcimId !== '0'">
                        <t-button class="distribute" @click="jumpDcim(el.url)" :disabled="isSync">{{lang.manage}}
                        </t-button>
                        <t-button class="distribute" @click="optItem('', 'free')" :disabled="isSync">{{lang.idle}}
                        </t-button>
                      </template>
                    </template>
                    <span class="opt-copy" v-if="el.copy" @click="copyText(el.value)">{{lang.copy}}</span>
                  </t-form-item>
                </div>
              </template>
            </t-col>
            <!-- 商品自定义字段 -->
            <t-col :xs="24" :xl="12" style="margin-top: 20px;" v-if="self_defined_field.length > 0">
              <p class="com-tit"><span>{{lang.client_custom_label32}}</span></p>
              <t-form :data="customForm" :rules="customRule" ref="customForm" label-align="top">
                <div class="config-item">
                  <t-form-item :label="item.field_name" v-for="item in self_defined_field" :key="item.id">
                    <!-- 下拉类型 -->
                    <t-select v-model="customForm[item.id + '']" :placeholder="item.description"
                      v-if="item.field_type === 'dropdown'" :disabled="isSync">
                      <t-option v-for="(items,indexs) in calcFieldOption(item.field_option)" :value="items"
                        :label="items" :key="indexs">
                      </t-option>
                    </t-select>
                    <!-- 勾选框类型 -->
                    <t-checkbox v-model="customForm[item.id + '']" :label="item.description"
                      v-else-if="item.field_type === 'tickbox'" :disabled="isSync">{{item.description}}
                    </t-checkbox>
                    <t-textarea v-model="customForm[item.id + '']" :placeholder="item.description"
                      v-else-if="item.field_type === 'textarea'" :disabled="isSync">
                    </t-textarea>
                    <t-input :placeholder="item.description" v-model="customForm[item.id + '']" v-else
                      :disabled="isSync">
                    </t-input>
                  </t-form-item>
                </div>
              </t-form>
            </t-col>
            <t-col :xs="24" :xl="12">
              <div class="footer-btn">
                <t-button theme="primary" type="submit" @click="updateUserInfo" :loading="isLoading"
                  v-permission="'auth_business_host_detail_save_basic_finance_info'">
                  {{lang.hold}}
                </t-button>
                <t-button theme="danger" @click="back" v-permission="'auth_business_host_detail_delete'">
                  {{lang.delete}}
                </t-button>
              </div>
            </t-col>
            <!-- 内页模块 -->
            <t-col :xs="24" :xl="12" style="margin-top: 20px;" v-if="isShowModule">
              <div class="config-box">
                <div class="content"></div>
              </div>
            </t-col>
            <!-- 上游信息 -->
            <t-col :xs="24" :xl="12" style="margin-top: 20px;" v-if="upData && upData.id">
              <p class="com-tit"><span>{{lang.upstream_info}}</span></p>
              <div class="item">
                <t-form-item :label="lang.buy_amount">
                  <div>{{upData.first_payment_amount}}</div>
                </t-form-item>
                <t-form-item :label="lang.renew_amount">
                  <div>{{upData.renew_amount}}</div>
                </t-form-item>
                <t-form-item :label="lang.billing_way">
                  <div>{{cycleObj[upData.billing_cycle]}}</div>
                </t-form-item>
                <t-form-item :label="lang.billing_cycle">
                  <div>{{upData.billing_cycle_name}}</div>
                </t-form-item>
                <t-form-item :label="lang.open_time">
                  <div>{{upData.active_time ? moment(upData.active_time * 1000).format('YYYY-MM-DD HH:mm:ss') : '--' }}
                  </div>
                </t-form-item>
                <t-form-item :label="lang.due_time">
                  <div>{{upData.due_time ? moment(upData.due_time * 1000).format('YYYY-MM-DD HH:mm:ss') : '--' }}</div>
                </t-form-item>
                <t-form-item :label="lang.status">
                  <div>{{lang[upData.status]}}</div>
                </t-form-item>
                <t-form-item :label="lang.host_name">
                  <div>{{upData.name}}</div>
                </t-form-item>
              </div>
            </t-col>
          </t-row>
        </t-form>
      </div>

    </t-card>
    <!-- 产品转移弹窗 -->
    <t-dialog :header="lang.host_transfer_text11" :close-on-overlay-click="false" :visible.sync="transferVisible"
      width="800" :footer="false">
      <t-form :data="transferForm" ref="transferInfo" :rules="transferRules" @submit="onTransferSubmit"
        label-align="top">
        <t-form-item :label="lang.host_transfer_text13" name="client_id">

          <!-- <t-select v-if="this.clientList" v-model="transferForm.client_id" :popup-props="popupProps" filterable :filter="filterMethod" :loading="searchLoading" reserve-keyword :on-search="remoteMethod">
            <t-option v-for="item in clientList" :value="item.id" :label="calcShow(item)" :key="item.id">
              #{{item.id}}-{{item.username ? item.username : (item.phone? item.phone: item.email)}}
              <span v-if="item.company">({{item.company}})</span>
            </t-option>
          </t-select> -->

          <com-choose-user @changeuser="changeProUser" class="com-clinet-choose"
            style="position: relative;right: 0; width: 100%;">
          </com-choose-user>

        </t-form-item>
        <t-form-item :label="lang.host_transfer_text14">
          <div>
            <t-table hover row-key="index" :data="hostList" bordered :columns="columns" class="table-box">
              <template #host_id="{row}">
                <span>{{row.id}}</span>
              </template>
              <template #product_name="{row}">
                <span>{{row.product_name}}</span>
              </template>
              <template #name="{row}">
                <span>{{row.name}}</span>
              </template>
              <template #notes="{row}">
                <span>{{row.notes || '--'}}</span>
              </template>
            </t-table>
          </div>
        </t-form-item>
        <t-form-item :label="lang.host_transfer_text17" v-if="link_host.length">
          <div>
            <t-table hover row-key="index" :data="link_host" :columns="columns" class="table-box" bordered>
              <template #host_id="{row}">
                <span>{{row.id}}</span>
              </template>
              <template #product_name="{row}">
                <span>{{row.product_name}}</span>
              </template>
              <template #name="{row}">
                <span>{{row.name}}</span>
              </template>
              <template #notes="{row}">
                <span>{{row.notes || '--'}}</span>
              </template>
            </t-table>
          </div>
        </t-form-item>
        <p class="red-tip" v-if="hostTips != ''">{{hostTips}}</p>
        <div class="tip-box" v-if="canTransfer">
          <p>{{lang.host_transfer_text18}}</p>
          <p>{{lang.host_transfer_text19}}</p>
        </div>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :disabled="!canTransfer"
            :loading="transfering">{{lang.host_transfer_text12}}</t-button>
          <t-button theme="default" variant="base" @click="transferVisible = false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>

    <!-- 删除 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
      <template slot="footer">
        <div class="common-dialog">
          <t-button @click="onConfirm" :loading="submitLoading">{{lang.sure}}</t-button>
          <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
        </div>
      </template>
    </t-dialog>
    <!-- 续费弹窗 -->
    <t-dialog :header="lang.renew" :visible.sync="renewVisible" class="renew-dialog" :footer="false">
      <div class="swiper" v-if="renewList.length >0 ">
        <div class="l-btn" @click="subIndex">
          <t-icon name="chevron-left"></t-icon>
        </div>
        <div class="m-box">
          <div class="swiper-item" v-for="(item,index) in renewList" :key="item.id"
            :class="{card: item.id === showId[0] || item.id === showId[1] || item.id === showId[2], active: item.id === curId}"
            @click="checkCur(item)">
            <p class="cycle">{{item.billing_cycle}}</p>
            <p class="price"><span>{{currency_prefix}}</span>{{item.price}}</p>
          </div>
        </div>
        <div class="r-btn" @click="addIndex">
          <t-icon name="chevron-right"></t-icon>
        </div>
      </div>
      <div class="com-f-btn">
        <div class="total">{{lang.total}}：
          <span class="price">
            <span class="symbol">{{currency_prefix}}</span>
            {{curRenew.price}}&nbsp;
          </span>
        </div>
        <t-checkbox v-model="pay">{{lang.mark_Paid}}&nbsp;</t-checkbox>
        <t-button theme="primary" @click="submitRenew" :loading="submitLoading">{{lang.sure_renew}}</t-button>
      </div>
    </t-dialog>

    <!-- 1-7 新增 -->
    <!-- 开通，取消暂停，删除 -->
    <t-dialog theme="warning" :header="optTilte" :visible.sync="moduleVisible" :close-on-overlay-click="false">
      <template slot="footer">
        <div class="common-dialog">
          <t-button @click="confirmModule" :loading="moduleLoading">{{lang.sure}}</t-button>
          <t-button theme="default" @click="moduleVisible=false">{{lang.cancel}}</t-button>
        </div>
      </template>
    </t-dialog>
    <!-- 停用 -->
    <t-dialog :header="lang.deactivate" :close-on-overlay-click="false" :visible.sync="suspendVisible" width="600"
      :footer="false">
      <t-form :data="suspendForm" ref="userInfo" @submit="onSubmit" :label-width="150" label-align="left">
        <t-form-item :label="lang.suspend_type">
          <t-select v-model="suspendForm.suspend_type">
            <t-option :value="item.value" :label="item.label" v-for="item in suspendType" :key="item.value">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.suspend_reason">
          <t-textarea v-model="suspendForm.suspend_reason"></t-textarea>
        </t-form-item>
        <t-form-item :label="lang.suspend_auto_time">
          <t-input-number v-model="suspendForm.suspend_auto_time" :placeholder="lang.suspend_auto_time_tip"
            :decimal-places="0" theme="normal" style="flex:1;" :min="0">
          </t-input-number>
          <t-select v-model="suspendForm.suspend_auto_time_unit" style="margin-left: 10px;width:120px ;">
            <t-option :value="item.value" :label="item.label" v-for="item in suspendAutoTimeUnit" :key="item.value">
            </t-option>
          </t-select>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="moduleLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="suspendVisible = false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 1-7 新增 end -->

    <!-- 手动资源 | DCIM资源 | 机柜 分配 -->
    <t-dialog :header="distributeTitle" :visible.sync="resourceDialog" width="80%" :footer="false">
      <div class="left-search">
        <template v-if="distributeType === 'manual'">
          <t-input v-model="resourceForm.keywords" class="search-input" :placeholder="lang.manual_text1"
            @keypress.enter.native="getResourcesList" :on-clear="clearKey" clearable></t-input>
          <t-select v-model="resourceForm.addon_manual_resource_supplier_id" :placeholder="lang.manual_text2" clearable>
            <t-option v-for="item in supplierList" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
          <t-select v-model="resourceForm.rel" :placeholder="lang.manual_concat" clearable class="finish">
            <t-option :value="0" :label="lang.manual_no" :key="0"></t-option>
            <t-option :value="1" :label="lang.manual_yes" :key="1"></t-option>
          </t-select>
          <t-button @click="getResourcesList" class="search">{{lang.query}}</t-button>
        </template>
        <template v-if="distributeType === 'dcim'">
          <t-input v-model="dcimForm.ip" class="search-input" :placeholder="`${lang.input}IP`"
            @keypress.enter.native="getDcimList" :on-clear="clearIp" clearable></t-input>
          <t-select v-model="dcimForm.status" :placeholder="lang.status" clearable>
            <t-option v-for="item in statusArr" :value="item.value" :label="item.label" :key="item.value">
            </t-option>
          </t-select>
          <t-select v-model="dcimForm.server_group_id" :placeholder="lang.group" clearable>
            <t-option v-for="item in dcimGroup" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
          <t-button @click="getDcimList" class="search">{{lang.query}}</t-button>
        </template>
      </div>
      <t-table row-key="index" :data="distributeType === 'manual' ? resourceList : dcimList" :columns="calcColumns"
        :loading="resourceLoading" :hover="true">
        <template #id="{row}">
          <a :href="row.dcim_url" target="_blank" class="common-look"
            v-if="distributeType === 'dcim'">{{row.id}}-{{row.wltag}}</a>
          <span v-if="distributeType === 'cabinet'">{{row.id}}</span>
        </template>
        <template #ip="{row}">
          {{row.ip[0]?.ipaddress}}
          <t-tooltip :show-arrow="false" theme="light" trigger="hover">
            <template slot="content">
              <div class="dcim-ip-list">
                <p>IP</p>
                <p v-for="(item,index) in row.ip" :key="index">{{item.ipaddress}}</p>
              </div>
            </template>
            <span v-if="row.ip.length > 1" class="common-look">({{row.ip.length}})</span>
          </t-tooltip>
        </template>
        <template #bw="{row}">
          {{(row.in_bw).replace('Mbps','') || 0}} / {{(row.out_bw).replace('Mbps','') || 0}}Mbps
        </template>
        <template #status="{row}">
          <span :class="`dcim-status-${row.status}`"
            v-if="distributeType === 'dcim'">{{calcDcimStatus(row.status)}}</span>
          <span v-if="distributeType === 'cabinet'"
            :class="{'dcim-status-2': row.status === 2, 'dcim-status-3': row.status === 1}">
            {{row.status === 1 ? lang.normal : (row.status === 2 ? lang.dcim_expire : lang.idle)}}
          </span>
        </template>
        <template #remarks="{row}">
          {{row.remarks || '--'}}
        </template>
        <template #group_name="{row}">
          {{row.group_name || '--'}}
        </template>
        <template #due_time="{row}">
          {{moment(row.due_time * 1000).format('YYYY/MM/DD HH:mm') }}
        </template>
        <template #user="{row}">
          <span v-if="row.client_name">
            {{row.client_name}}
            <span v-if="row.host_name">({{row.host_name}})</span>
          </span>
          <span v-else>--</span>
        </template>
        <template #configuration="{row}">
          <span v-if="row.configuration">{{row.configuration}}</span>
          <span v-else>--</span>
        </template>
        <template #notes="{row}">
          <span v-if="row.notes">{{row.notes}}</span>
          <span v-else>--</span>
        </template>
        <template #power_status="{row}">
          {{calcStatus(row.power_status)}}
        </template>
        <template #cost="{row}">
          {{currency_prefix}}{{row.cost}}
        </template>
        <template #host_id="{row}">
          <a :href="`${baseUrl}/host_detail.htm?client_id=${row.client_id}&id=${row.host_id}`" target="_blank"
            class="common-look" v-if="row.host_id">{{row.host_id}}</a>
          <span v-else>--</span>
        </template>
        <template #client_id="{row}">
          <a :href="`${baseUrl}/client_detail.htm?id=${row.client_id}`" target="_blank" class="common-look"
            v-if="row.client_id">{{row.client_id}}</a>
          <span v-else>--</span>
        </template>
        <template #opt="{row}">
          <template v-if="distributeType === 'manual'">
            <!-- 不用先空闲再分配 -->
            <!-- <template v-if="curResourcesId">
              <t-tooltip :content="lang.idle" :show-arrow="false" theme="light" v-if="row.host_id">
                <a class="common-look" @click="optItem(row, 'free')">
                  <svg class="common-look">
                    <use xlink:href="#icon-free">
                    </use>
                  </svg>
                </a>
              </t-tooltip>
            </template> -->
            <!-- <template v-else> </template> -->
            <t-tooltip :content="lang.distribute" :show-arrow="false" theme="light" v-if="!row.host_id">
              <a class="common-look" @click="optItem(row, 'allot')">
                <svg class="common-look">
                  <use xlink:href="#icon-allot">
                  </use>
                </svg>
              </a>
            </t-tooltip>
            <t-tooltip :content="lang.idle" :show-arrow="false" theme="light" v-else>
              <a class="common-look" @click="optItem(row, 'free')">
                <svg class="common-look">
                  <use xlink:href="#icon-free">
                  </use>
                </svg>
              </a>
            </t-tooltip>
          </template>
          <template v-if="distributeType === 'dcim'">
            <t-tooltip :content="lang.distribute" :show-arrow="false" theme="light" v-if="row.status == '1'">
              <a class="common-look" @click="optItem(row, 'allot')">
                <svg class="common-look">
                  <use xlink:href="#icon-allot">
                  </use>
                </svg>
              </a>
            </t-tooltip>
          </template>
          <template v-if="distributeType === 'cabinet'">
            <template v-if="curResourcesId">
              <!-- <t-tooltip :content="lang.idle" :show-arrow="false" theme="light" v-if="row.host_id">
                <a class="common-look" @click="optItem(row, 'cabinetFree')">
                  <svg class="common-look">
                    <use xlink:href="#icon-free">
                    </use>
                  </svg>
                </a>
              </t-tooltip> -->
            </template>
            <template v-else>
              <t-tooltip :content="lang.distribute" :show-arrow="false" theme="light" v-if="row.status === 3">
                <a class="common-look" @click="optItem(row, 'allot')">
                  <svg class="common-look">
                    <use xlink:href="#icon-allot">
                    </use>
                  </svg>
                </a>
              </t-tooltip>
              <!-- <t-tooltip :content="lang.idle" :show-arrow="false" theme="light" v-if="row.status === 1">
                <a class="common-look" @click="optItem(row, 'cabinetFree')">
                  <svg class="common-look">
                    <use xlink:href="#icon-free">
                    </use>
                  </svg>
                </a>
              </t-tooltip> -->
            </template>
          </template>
        </template>
      </t-table>
      <t-pagination show-jumper :total="dcimTotal" v-if="dcimTotal" :current="dcimForm.page" :page-size="dcimForm.limit"
        :page-size-options="pageSizeOptions" :on-change="changePage"></t-pagination>
      <t-pagination show-jumper :total="resourceTotal" v-if="resourceTotal" :current="resourceForm.page"
        :page-size="resourceForm.limit" :page-size-options="pageSizeOptions"
        :on-change="changeResourcePage"></t-pagination>
      <t-pagination show-jumper :total="cabinetTotal" v-if="cabinetTotal" :current="cabinetForm.page"
        :page-size="resourceForm.limit" :page-size-options="pageSizeOptions" :on-change="changeCabinetPage">
      </t-pagination>
    </t-dialog>
    <!-- 分配/空闲 -->
    <t-dialog theme="warning" :header="resourceTitle" :visible.sync="resourceVisible">
      <t-checkbox v-model="checkDcimStatus" class="dcim-model-status" v-if="curDcimId !== '0'">
        {{lang.distribute_tip1}}
      </t-checkbox>
      <template slot="footer">
        <div class="common-dialog">
          <t-button @click="handlerResource" :loading="submitLoading">{{lang.sure}}</t-button>
          <t-button theme="default" @click="resourceVisible=false">{{lang.cancel}}</t-button>
        </div>
      </template>
    </t-dialog>
    <!-- 流量弹窗 -->
    <t-dialog :header="lang.module_flow_place_order" :visible.sync="flowDialog" :footer="false" class="flow-dialog"
      width="720">
      <div class="con">
        <div class="p-item" v-for="item in useableFlowList" :key="item.id" :class="{active: item.id === curPackageId}"
          @click="choosePackage(item)">
          <p class="tit">{{item.name}}</p>
          <p class="qty">{{item.capacity}}G</p>
          <p class="price">{{currency_prefix}}{{item.price | filterMoney}}</p>
          <t-icon name="check"></t-icon>
        </div>
      </div>
      <div class="com-f-btn">
        <t-button theme="primary" type="submit" :loading="submitLoading"
          @click="handlerPackage(false)">{{lang.module_place_order}}</t-button>
        <t-button theme="primary" :loading="submitLoading"
          @click="handlerPackage(true)">{{lang.module_order_redirect}}</t-button>
        <t-button theme="default" variant="base" @click="flowDialog = false">{{lang.cancel}}</t-button>
      </div>
    </t-dialog>
    <!-- 防御弹窗 -->
    <t-dialog :header="lang.defence_detail" :visible.sync="defenceDialog" :footer="false" class="flow-dialog"
      width="720">
      <div
        style="display: flex; justify-content: flex-end; align-items: center; column-gap: 10px; margin-bottom: 10px;">
        <t-input style="width: 240px;" v-model="defenceParams.keywords" @keypress.enter.native="searchDefence"
          :on-clear="clearDefenceKey" clearable></t-input>
        <t-button @click="searchDefence">{{lang.query}}</t-button>
      </div>
      <t-table hover row-key="id" :data="defenceList" :loading="defenceLoading" :columns="defenceColumns"
        :hide-sort-tips="true" @sort-change="sortChange">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #host_ip="{row}">
          <span>{{row.host_ip || '--'}}</span>
        </template>
        <template #defense_peak="{row}">
          <span>{{row.defense_peak || '--'}}</span>
        </template>
      </t-table>
      <t-pagination show-jumper v-if="defenceTotal" :total="defenceTotal" :page-size="defenceParams.limit"
        :page-size-options="pageSizeOptions" :on-change="changeDefencePage">
      </t-pagination>
    </t-dialog>
    <safe-confirm ref="safeRef" :password.sync="admin_operate_password" @confirm="hadelSafeConfirm"></safe-confirm>

    <!-- 重置授权先关 -->
    <!-- 详情弹窗 -->
    <t-dialog :header="lang.authreset_title" :visible.sync="detailVisible" width="600px" :destroy-on-close="true">
      <div class="detail-content" v-loading="detailLoading">
        <t-form ref="auditForm" :data="auditForm" :rules="auditFormRules" label-align="top">
          <!-- 当前授权信息 -->
          <div class="detail-section">
            <div class="auth-info-grid">
              <div class="auth-info-item">
                <label>{{lang.authreset_original_info}}:</label>
                <div class="auth-value">
                  <div>{{lang.authreset_ip_address}}: {{auditForm.authorize?.ip || '--'}}</div>
                  <div>{{lang.authreset_domain}}: {{auditForm.authorize?.domain || '--'}}</div>
                </div>
              </div>
            </div>
          </div>
          <!-- 申请变更详情 -->
          <p>{{lang.authreset_change_details}}</p>
          <t-form-item prop="new_ip" :label="lang.authreset_ip">
            <t-input v-model="auditForm.new_ip" :placeholder="lang.authreset_ip">
            </t-input>
          </t-form-item>
          <t-form-item prop="new_domain" :label="lang.authreset_tooltip">
            <t-input v-model="auditForm.new_domain" :placeholder="lang.authreset_admin_notes_placeholder">
            </t-input>
          </t-form-item>
          <!-- 变更历史 -->
          <div class="detail-section">
            <p class="small-tit">{{lang.authreset_change_history}}</p>
            <!-- 统计信息 -->
            <div class="history-stats" v-if="auditForm.recent_month_total">
              <p>{{lang.authreset_tooltip_recent_total}}：{{auditForm.recent_month_total || 0}}{{lang.authreset_count}}
              </p>
              <p>{{lang.authreset_tooltip_type_distribution}}：</p>
              <div class="stats-grid">
                <div class="stat-item">
                  <div class="stat-label">{{lang.authreset_ip_change_count}}：</div>
                  <div class="stat-value">{{auditForm.type_distribution.ip_change || 0}}{{lang.authreset_count}}</div>
                </div>
                <div class="stat-item">
                  <div class="stat-label">{{lang.authreset_domain_change_count}}：</div>
                  <div class="stat-value">{{auditForm.type_distribution.domain_change || 0}}{{lang.authreset_count}}
                  </div>
                </div>
                <div class="stat-item">
                  <div class="stat-label">{{lang.authreset_ip_domain_change_count}}：</div>
                  <div class="stat-value">{{auditForm.type_distribution.ip_domain_change || 0}}{{lang.authreset_count}}
                  </div>
                </div>
                <div class="stat-item">
                  <div class="stat-label">{{lang.authreset_general_reset_count}}：</div>
                  <div class="stat-value">{{auditForm.type_distribution.general_reset || 0}}{{lang.authreset_count}}
                  </div>
                </div>
              </div>
            </div>
            <div v-if="!auditForm" class="no-history">{{lang.authreset_no_history}}</div>
          </div>
          <!-- 审核操作区域 -->
          <div class="review-section">
            <!-- 管理员备注 -->
            <t-form-item prop="admin_notes" :label="lang.admin_notes">
              <t-textarea v-model="auditForm.admin_notes" :placeholder="lang.authreset_admin_notes_placeholder"
                :maxlength="300" rows="3">
              </t-textarea>
            </t-form-item>
            <!-- 快捷备注模板 -->
            <t-form-item label=" " class="empty-item">
              <div class="quick-replies-section" v-if="notesQuickReplies.length">
                <div class="quick-replies">
                  <t-tag v-for="item in notesQuickReplies" :key="item.id"
                    @click="selectQuickAdminNote(item.description)" class="quick-reply-tag">
                    {{item.description}}
                  </t-tag>
                  <t-button size="small" variant="text" @click="manageQuickReplies('notes')">
                    {{lang.manage}}
                  </t-button>
                </div>
              </div>
            </t-form-item>
          </div>
      </div>
      <template slot="footer">
        <t-button theme="primary" @click="submitAuditForm" :loading="reviewLoading">{{lang.authreset_sure}}</t-button>
        <t-button theme="default" @click="closeDetailDialog">{{lang.cancel}}</t-button>
      </template>
      </t-form>
    </t-dialog>

    <!-- 快捷回复管理弹窗 -->
    <t-dialog :header="lang.admin_notes" :visible.sync="quickReplyVisible" :footer="false" width="600px">
      <div class="quick-reply-manage">
        <div class="quick-reply-list">
          <div class="quick-reply-item" v-for="item in currentQuickReplies" :key="item.id">
            <div class="reply-content">{{item.description}}</div>
            <div class="reply-actions">
              <t-button size="small" theme="primary" variant="text" @click="editQuickReply(item)">
                {{lang.edit}}
              </t-button>
              <t-button size="small" theme="danger" variant="text" @click="deleteQuickReplyItem(item)">
                {{lang.delete}}
              </t-button>
            </div>
          </div>
        </div>
        <div class="quick-reply-form">
          <t-form :rules="auditFormRules" :data="quickReplyForm" ref="quickReplyForm" @submit="saveQuickReply">
            <t-form-item name="description">
              <t-textarea v-model="quickReplyForm.description" :placeholder="lang.authreset_quick_reply_placeholder"
                :maxlength="300" rows="3">
              </t-textarea>
            </t-form-item>
            <div class="com-f-btn">
              <t-button theme="primary" @click="saveQuickReply" :loading="quickReplyLoading">
                {{quickReplyForm.id ? lang.authreset_update : lang.add}}
              </t-button>
              <t-button theme="default" variant="base" @click="closeQuickReplyDialog">{{lang.cancel}}</t-button>
            </div>
          </t-form>
        </div>
      </div>
    </t-dialog>
    <!-- 删除 -->
    <t-dialog theme="warning" :header="lang.authreset_delete_confirm" :visible.sync="delVisble">
      <template slot="footer">
        <t-button theme="primary" @click="sureDel" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisble=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/components/comChooseUser/comChooseUser.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/safeConfirm/safeConfirm.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/flowPacket/flowPacket.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/host_detail.js"></script>
{include file="footer"}

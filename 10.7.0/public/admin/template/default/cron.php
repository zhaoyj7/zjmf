{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="cron" v-cloak>
  <com-config>
    <t-form :data="formData" :label-width="150" ref="formValidatorStatus" label-align="top" @submit="onSubmit"
      label-width="200" :rules="rules">
      <t-card class="list-card-container">
        <!-- <p class="com-h-tit">{{lang.automation}}</p> -->
        <div class="box first-box">
          <p class="com-tit first-text-color"><span>{{lang.automation}}</span></p>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }" class="custom-row">
            <t-col :xs="12" :xl="6">
              <div class="item">
                <p class="tit first-text-color">
                  {{lang.task_queue_commands}}
                  <span class="status" :class="formData.cron_task_status ==='success'? 'success' : 'error'">
                    <t-icon
                      :name="formData.cron_task_status ==='success'? 'check-circle-filled' : 'error-circle-filled'"
                      size="16"></t-icon>
                    <span>{{formData.cron_task_status === 'success' ? lang.task_queue_normal : lang.task_queue_abnormal}}</span>
                  </span>
                </p>
                <p class="code-text third-text-color">
                  {{formData.cron_task_shell}}
                </p>
              </div>
            </t-col>
            <t-col :xs="12" :xl="6">
              <div class="item">
                <p class="tit first-text-color">
                  {{lang.automation_scripts}}
                  <span class="status" :class="formData.cron_status ==='success'? 'success' : 'error'">
                    <t-icon :name="formData.cron_status ==='success'? 'check-circle-filled' : 'error-circle-filled'"
                      size="16"></t-icon>
                    <span>{{formData.cron_status === 'success' ? lang.automation_normal : lang.automation_abnormal}}</span>
                  </span>
                </p>
                <p class="code-text third-text-color">
                  {{formData.cron_shell}}
                </p>
              </div>
            </t-col>
          </t-row>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }" class="custom-row">
            <t-col :xs="12" :xl="6">
              <div class="item">
                <p class="tit">
                  {{lang.demand_task_command}}
                  <span class="status" :class="formData.cron_on_demand_cron_status ==='success'? 'success' : 'error'">
                    <t-icon
                      :name="formData.cron_on_demand_cron_status ==='success'? 'check-circle-filled' : 'error-circle-filled'"
                      size="16"></t-icon>
                    <span>{{formData.cron_on_demand_cron_status === 'success' ? lang.demand_normal : lang.demand_abnormal}}</span>
                  </span>
                </p>
                <p class="code-text third-text-color">
                  {{formData.cron_on_demand_cron_shell}}
                </p>
              </div>
            </t-col>
            <t-col :xs="12" :xl="6">
              <div class="item">
                <p class="tit">
                  {{lang.notice_task_command}}
                  <t-switch size="large" style="margin-left: 8px;" :custom-value="[1,0]"
                    v-model="formData.notice_independent_task_enabled">
                  </t-switch>
                  <template v-if="formData.notice_independent_task_enabled === 1">
                    <span class="status" :class="formData.cron_task_notice_status ==='success'? 'success' : 'error'">
                      <t-icon
                        :name="formData.cron_task_notice_status ==='success'? 'check-circle-filled' : 'error-circle-filled'"
                        size="16"></t-icon>
                      <span>{{formData.cron_task_notice_status === 'success' ? lang.notice_normal : lang.notice_abnormal}}</span>
                    </span>
                  </template>
                </p>
                <template v-if="formData.notice_independent_task_enabled === 1">
                  <p class="code-text third-text-color">
                    {{formData.cron_task_notice_shell}}
                  </p>
                </template>
              </div>
            </t-col>
          </t-row>
        </div>
      </t-card>
      <t-card class="list-card-container">
        <div class="item-box">
          <p class="com-tit"><span>{{lang.setting_text75}}</span></p>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, xxl: 56 }">
            <t-col :xs="12" :xl="4">
              <t-form-item :label="lang.task_execution_time">
                <t-select v-model="formData.cron_day_start_time" :placeholder="lang.select" style="width: auto;">
                  <t-option v-for="item in timeArr" :value="item.value" :label="item.label" :key="item.value">
                  </t-option>
                </t-select>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="4">
              <t-form-item>
                <template slot="label">
                  <div class="custom-label">
                    <span>{{lang.setting_text76}}</span>
                    <t-switch size="large" :custom-value="[1,0]" v-model="formData.task_fail_retry_open">
                    </t-switch>
                  </div>
                </template>
                <span class="tip">{{lang.setting_text77}}</span>
                <t-input-number v-model="formData.task_fail_retry_times" :min="0" :auto-width="true"></t-input-number>
                <span>{{lang.setting_text78}},{{lang.setting_text79}}</span>
              </t-form-item>
            </t-col>
          </t-row>
        </div>
      </t-card>
      <t-card class="list-card-container">
        <div class="item-box">
          <p class="com-tit"><span>{{lang.module}}</span></p>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 56 }">
            <t-col :xs="12" :xl="4">
              <t-form-item>
                <template slot="label">
                  <div class="custom-label">
                    <span>{{lang.product_suspend}}</span>
                    <t-switch size="large" :custom-value="[1,0]" v-model="formData.cron_due_suspend_swhitch">
                    </t-switch>
                  </div>
                </template>
                <span class="tip">{{lang.after_due}}</span>
                <t-input-number v-model="formData.cron_due_suspend_day" :min="0" :auto-width="true"></t-input-number>
                <span>{{lang.tip12}}</span>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="4">
              <t-form-item :label="lang.product_delete">
                <template slot="label">
                  <div class="custom-label">
                    <span>{{lang.product_delete}}</span>
                    <t-switch size="large" :custom-value="[1,0]" v-model="formData.cron_due_terminate_swhitch">
                    </t-switch>
                  </div>
                </template>
                <span>{{lang.after_due}}</span>
                <t-input-number v-model="formData.cron_due_terminate_day" :min="0" :auto-width="true"></t-input-number>
                <span>{{lang.tip14}}</span>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="4">
              <t-form-item>
                <template slot="label">
                  <div class="custom-label">
                    <span>{{lang.product_relieve_suspend}}</span>
                    <t-switch size="large" :custom-value="[1,0]" v-model="formData.cron_due_unsuspend_swhitch">
                    </t-switch>
                  </div>
                </template>
                <span>{{lang.tip13}}</span>
              </t-form-item>
            </t-col>
          </t-row>
        </div>
      </t-card>
      <t-card class="list-card-container">
        <div class="item-box">
          <p class="com-tit"><span>{{lang.financial}}</span></p>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 56 }">
            <t-col :xs="12" :xl="4">
              <t-form-item :label="lang.order_unpaid_notice" name="cron_due_renewal_first_day">
                <template slot="label">
                  <div class="custom-label">
                    <span>{{lang.order_unpaid_notice}}</span>
                    <t-switch size="large" :custom-value="[1,0]" v-model="formData.cron_order_overdue_swhitch ">
                    </t-switch>
                  </div>
                </template>
                <span class="tip">{{lang.after_orders}}</span>
                <t-input-number v-model="formData.cron_order_overdue_day" :auto-width="true"></t-input-number>
                <span>{{lang.day_remind}}</span>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="4">
              <t-form-item :label="lang.order_auto_del" name="cron_order_unpaid_delete_swhitch">
                <template slot="label">
                  <div class="custom-label">
                    <span>{{lang.order_auto_del}}</span>
                    <t-switch size="large" :custom-value="[1,0]" v-model="formData.cron_order_unpaid_delete_swhitch ">
                    </t-switch>
                  </div>
                </template>
                <span class="tip">{{lang.no_pay}}</span>
                <t-input-number v-model="formData.cron_order_unpaid_delete_day" :auto-width="true"></t-input-number>
                <span>{{lang.day_del}}</span>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="4">
              <t-form-item :label="lang.host_renewal_one" name="cron_due_renewal_first_day">
                <template slot="label">
                  <div class="custom-label">
                    <span>{{lang.host_renewal_one}}</span>
                    <t-switch size="large" :custom-value="[1,0]" v-model="formData.cron_due_renewal_first_swhitch ">
                    </t-switch>
                  </div>
                </template>
                <span class="tip">{{lang.before_due}}</span>
                <t-input-number v-model="formData.cron_due_renewal_first_day" :auto-width="true"></t-input-number>
                <span>{{lang.day_remind}}</span>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="4">
              <t-form-item :label="lang.host_renewal_two">
                <template slot="label">
                  <div class="custom-label">
                    <span>{{lang.host_renewal_two}}</span>
                    <t-switch size="large" :custom-value="[1,0]" v-model="formData.cron_due_renewal_second_swhitch">
                    </t-switch>
                  </div>
                </template>
                <span>{{lang.before_due}}</span>
                <t-input-number v-model="formData.cron_due_renewal_second_day" :auto-width="true"></t-input-number>
                <span>{{lang.day_remind}}</span>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="4">
              <t-form-item :label="lang.host_overdue_one">
                <template slot="label">
                  <div class="custom-label">
                    <span>{{lang.host_overdue_one}}</span>
                    <t-switch size="large" :custom-value="[1,0]" v-model="formData.cron_overdue_first_swhitch">
                    </t-switch>
                  </div>
                </template>
                <span>{{lang.after_due}}</span>
                <t-input-number v-model="formData.cron_overdue_first_day" :auto-width="true"></t-input-number>
                <span>{{lang.day_remind}}</span>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="4">
              <t-form-item :label="lang.host_overdue_two">
                <template slot="label">
                  <div class="custom-label">
                    <span>{{lang.host_overdue_two}}</span>
                    <t-switch size="large" :custom-value="[1,0]" v-model="formData.cron_overdue_second_swhitch">
                    </t-switch>
                  </div>
                </template>
                <span>{{lang.after_due}}</span>
                <t-input-number v-model="formData.cron_overdue_second_day" :auto-width="true"></t-input-number>
                <span>{{lang.day_remind}}</span>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="4">
              <t-form-item :label="lang.host_overdue_three">
                <template slot="label">
                  <div class="custom-label">
                    <span>{{lang.host_overdue_three}}</span>
                    <t-switch size="large" :custom-value="[1,0]" v-model="formData.cron_overdue_third_swhitch">
                    </t-switch>
                  </div>
                </template>
                <span>{{lang.after_due}}</span>
                <t-input-number v-model="formData.cron_overdue_third_day" :auto-width="true"></t-input-number>
                <span>{{lang.day_remind}}</span>
              </t-form-item>
            </t-col>
          </t-row>
        </div>
      </t-card>
      <t-card class="list-card-container">
        <div class="item-box">
          <p class="com-tit"><span>{{lang.setting_text80}}</span></p>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, xxl: 56 }">
            <t-col :xs="12" :xl="4">
              <t-form-item :label="lang.setting_text81">
                <template slot="label">
                  <div class="custom-label">
                    <span>{{lang.setting_text81}}</span>
                    <t-switch size="large" :custom-value="[1,0]"
                      v-model="formData.cron_system_log_delete_swhitch"></t-switch>
                  </div>
                </template>
                <span>{{lang.setting_text82}}</span>
                <t-input-number v-model="formData.cron_system_log_delete_day" :auto-width="true" :min="0">
                </t-input-number>
                <span>{{lang.setting_text83}},{{lang.setting_text84}}</span>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="4">
              <t-form-item :label="lang.setting_text85">
                <template slot="label">
                  <div class="custom-label">
                    <span>{{lang.setting_text85}}</span>
                    <t-switch size="large" :custom-value="[1,0]" v-model="formData.cron_sms_log_delete_swhitch">
                    </t-switch>
                  </div>
                </template>
                <span>{{lang.setting_text82}}</span>
                <t-input-number v-model="formData.cron_sms_log_delete_day" :min="0" :auto-width="true"></t-input-number>
                <span>{{lang.setting_text83}},{{lang.setting_text84}}</span>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="4">
              <t-form-item :label="lang.setting_text86">
                <template slot="label">
                  <div class="custom-label">
                    <span>{{lang.setting_text86}}</span>
                    <t-switch size="large" :custom-value="[1,0]" v-model="formData.cron_email_log_delete_swhitch">
                    </t-switch>
                  </div>
                </template>
                <span>{{lang.setting_text82}}</span>
                <t-input-number v-model="formData.cron_email_log_delete_day" :min="0"
                  :auto-width="true"></t-input-number>
                <span>{{lang.setting_text83}},{{lang.setting_text84}}</span>
              </t-form-item>
            </t-col>
          </t-row>
        </div>
      </t-card>
      <t-form-item class="com-is-fixed" :class="{'has-shadow': !footerInView}">
        <t-button theme="primary" type="submit" :loading="submitLoading"
          v-permission="'auth_management_cron_save_cron'">{{lang.hold}}</t-button>
        <!-- <t-button theme="default" variant="base">{{lang.close}}</t-button> -->
      </t-form-item>
    </t-form>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/manage.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/cron.js"></script>
{include file="footer"}

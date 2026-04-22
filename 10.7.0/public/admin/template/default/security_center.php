{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-system configuration-login" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="safe-box">
        <h3>{{lang.setting_text106}}</h3>
        <t-form label-align="top">
          <p class="com-tit"><span>{{ lang.setting_text107 }}</span></p>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item :label="lang.setting_text108" style="width: 100%;">
                <t-input readonly disabled v-model="verifyFormData.name"></t-input>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item :label="lang.setting_text109" style="width: 100%;">
                <t-input v-model="nickname" :readonly="!editName" @blur="saveNikename">
                  <template #suffix-icon>
                    <t-icon name="edit-1" v-if="!editName" :style="{ cursor: 'pointer',color:'var(--td-brand-color)'}"
                      @click="handelChangeNikename"></t-icon>
                  </template>
                </t-input>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="admin_role_name" :label="lang.setting_text119" style="width: 100%;">
                <t-input v-model="admin_role_name" readonly disabled>
                </t-input>
              </t-form-item>
            </t-col>
          </t-row>
          <p class="com-tit"><span>{{ lang.setting_text110 }}</span></p>
          <t-row :gutter="{ xs: 8, sm: 16, md: 24, lg: 32, xl: 32, xxl: 60 }">
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="phone" :label="lang.phone" style="width: 100%;">
                <t-input :default-value="verifyFormData.phone" readonly :disabled="prohibit_admin_bind_phone == 1">
                  <template #suffix-icon>
                    <t-icon name="edit-1" :style="{ cursor: 'pointer',color:'var(--td-brand-color)'}"
                      v-if="prohibit_admin_bind_phone == 0" @click="handelVerify('phone')"></t-icon>
                  </template>
                </t-input>
              </t-form-item>
            </t-col>

            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="email" :label="lang.sub_account_text3" style="width: 100%;">
                <t-input :default-value="verifyFormData.email" readonly :disabled="prohibit_admin_bind_email == 1">
                  <template #suffix-icon>
                    <t-icon name="edit-1" :style="{ cursor: 'pointer',color:'var(--td-brand-color)'}"
                      v-if="prohibit_admin_bind_email == 0" @click="handelVerify('email')"></t-icon>
                  </template>
                </t-input>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="totp_bind" :label="lang.ssetting_text118" style="width: 100%;">
                <t-input :default-value="totp_bind == 1 ? lang.setting_text125 : lang.setting_text126" readonly>
                  <template #suffix-icon>
                    <span :style="{ cursor: 'pointer',color:'var(--td-brand-color)',marginLeft:'5px'}"
                      @click="handelTotp">{{totp_bind == 1 ? lang.setting_text133 : lang.setting_text132}}</span>
                  </template>
                </t-input>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.setting_text26" style="width: 100%;">
                <t-input default-value="*********" readonly>
                  <template #suffix-icon>
                    <t-icon name="edit-1" :style="{ cursor: 'pointer',color:'var(--td-brand-color)'}"
                      @click="handelChangePass(1)"></t-icon>
                  </template>
                </t-input>
              </t-form-item>
            </t-col>
            <t-col :xs="12" :xl="3" :md="6">
              <t-form-item name="lang_admin" :label="lang.setting_text27" style="width: 100%;">
                <t-input default-value="*********" readonly>
                  <template #suffix-icon>
                    <t-icon name="edit-1" :style="{ cursor: 'pointer',color:'var(--td-brand-color)'}"
                      @click="handelChangePass(2)"></t-icon>
                  </template>
                </t-input>
              </t-form-item>
            </t-col>
          </t-row>
        </t-form>

      </div>
    </t-card>

    <!-- 修改密码弹窗开始 -->
    <t-dialog :visible.sync="editPassVisible"
      :header="type === 1 ? lang.setting_text35 : set_operate_password ? lang.setting_text36 : lang.setting_text37"
      :on-close="editPassClose" :footer="false" width="600">
      <t-form :data="editPassFormData" ref="userDialog" @submit="onSubmit" reset-type="initial">
        <t-form-item :label="lang.setting_text28" name="origin_password"
          v-if="(type === 2 && set_operate_password) || type === 1"
          :rules="[{ required: true , message: `${lang.input}${lang.setting_text28}`, type: 'error' }]">
          <t-input :placeholder="`${lang.input}${lang.setting_text28}`" type="password"
            v-model="editPassFormData.origin_password">
          </t-input>
        </t-form-item>
        <t-form-item :label="lang.setting_text30" name="password"
          :rules="type === 1 ? [{ required: true , message: `${lang.input}${lang.setting_text30}`, type: 'error' },{ pattern: /^[\w@!#$%^&*()+-_]{6,32}$/, message: lang.verify8 + '，' + lang.verify14 + '6~32', type: 'warning' }] : [{ required: true , message: `${lang.input}${lang.setting_text30}`, type: 'error' }]">
          <t-input :placeholder="`${lang.input}${lang.setting_text30}`" type="password"
            v-model="editPassFormData.password">
          </t-input>
        </t-form-item>
        <t-form-item :label="lang.surePassword" name="repassword"
          :rules="[{ required: true, message: `${lang.input}${lang.surePassword}`, type: 'error' },{ validator: checkPwd, trigger: 'blur' }]">
          <t-input :placeholder="`${lang.input}${lang.surePassword}`" type="password"
            v-model="editPassFormData.repassword">
          </t-input>
        </t-form-item>
        <div class="com-f-btn" style="text-align: right;">
          <t-button theme="primary" type="submit" :loading="loading">{{lang.sure}}</t-button>
          <t-button theme="default" variant="base" @click="editPassClose">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 修改密码弹窗结束 -->

    <!-- 验证手机号/邮箱弹窗 -->
    <t-dialog :visible.sync="verifyVisible"
      :header="verifyType === 'phone' ?  lang.setting_text111 : lang. setting_text112" :on-close="verifyClose"
      :footer="false" width="600">
      <t-form :data="verifyFormData" ref="verifyDialog" @submit="onVerifySubmit" reset-type="initial">
        <t-form-item :label="lang.setting_text113" v-if="verifyType === 'phone'">
          <t-input readonly v-model="verifyFormData.phone" disabled>
          </t-input>
        </t-form-item>
        <t-form-item :label="lang.setting_text121" v-else>
          <t-input readonly v-model="verifyFormData.email" disabled>
          </t-input>
        </t-form-item>
        <t-form-item name="code" :label="lang.setting_text114" :rules="[{required:true,message:lang.setting_text120}]">
          <div class="verify-box">
            <t-input v-model="verifyFormData.code" :placeholder="lang.setting_text120"></t-input>
            <t-button theme="primary" @click="getVerifyCode" style="flex-shrink: 0;"
              :disabled="isVerifySending">{{isVerifySending ? (verifyCodeTime + lang.setting_text124) : lang.setting_text115}}
            </t-button>
          </div>
        </t-form-item>
        <div class="com-f-btn" style="text-align: right;">
          <t-button theme="primary" type="submit" :loading="verifyLoading">{{lang.sure}}</t-button>
          <t-button theme="default" variant="base" @click="verifyClose">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>

    <!-- 修改手机号/邮箱弹窗 -->
    <t-dialog :visible.sync="changeVisible"
      :header="verifyType === 'phone' ?  lang.setting_text116 : lang. setting_text117" :on-close="changeClose"
      :footer="false" width="600">
      <t-form :data="sendCodeParams" ref="changeDialog" @submit="onChangeSubmit" reset-type="initial">
        <t-form-item :label="lang.setting_text122" name="phone" v-if="verifyType === 'phone'">
          <t-select v-model="sendCodeParams.phone_code" filterable style="width: 100px" :placeholder="lang.phone_code">
            <t-option v-for="item in country" :value="item.phone_code" :label="item.name_zh + '+' + item.phone_code"
              :key="item.name">
            </t-option>
          </t-select>
          <t-input :placeholder="lang.setting_text122" v-model="sendCodeParams.phone"></t-input>
        </t-form-item>
        <t-form-item :label="lang.setting_text123" name="email" v-if="verifyType === 'email'">
          <t-input v-model="sendCodeParams.email" :placeholder="lang.setting_text123"></t-input>
        </t-form-item>
        <t-form-item :label="lang.setting_text114" name="code">
          <div class="verify-box">
            <t-input v-model="sendCodeParams.code" :placeholder="lang.setting_text120"></t-input>
            <t-button theme="primary" @click="getChangeCode" style="flex-shrink: 0;"
              :disabled="isChangeSending">{{isChangeSending ? (changeCodeTime + lang.setting_text124) : lang.setting_text115}}
            </t-button>
          </div>
        </t-form-item>

        <div class="com-f-btn" style="text-align: right;">
          <t-button theme="primary" type="submit" :loading="changeLoading">{{lang.sure}}</t-button>
          <t-button theme="default" variant="base" @click="changeClose">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>

    <!-- 解绑TOTP动态口令 -->
    <t-dialog :visible.sync="unbindTotpVisible" :header="lang.setting_text134" :on-close="unbindTotpClose"
      :footer="false" width="600">
      <t-form :data="unbindTotpFormData" ref="unbindTotpDialog" @submit="onUnbindTotpSubmit" reset-type="initial">
        <t-form-item :label="lang.setting_text136">
          <t-select v-model="verifyType" :placeholder="lang.setting_text135" style="width: 100%;">
            <t-option value="phone" :label="lang.setting_text111"></t-option>
            <t-option value="email" :label="lang.setting_text112"></t-option>
            <t-option value="totp" :label="lang.ssetting_text118"></t-option>
          </t-select>
        </t-form-item>
        <t-form-item name="code" :label="lang.setting_text114" :rules="[{required:true,message:lang.setting_text120}]">
          <div class="verify-box">
            <t-input style="width: 100%;" v-model="unbindTotpFormData.code" :placeholder="lang.setting_text120">
            </t-input>
            <t-button v-if="verifyType ==='phone' || verifyType === 'email' " theme="primary" @click="getVerifyCode"
              style="flex-shrink: 0;"
              :disabled="isVerifySending">{{isVerifySending ? (verifyCodeTime + lang.setting_text124) : lang.setting_text115}}
            </t-button>
          </div>
        </t-form-item>
        <div class="com-f-btn" style="text-align: right;">
          <t-button theme="primary" type="submit">{{lang.sure}}</t-button>
          <t-button theme="default" variant="base" @click="unbindTotpClose">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>

    <!-- TOTP动态口令 -->
    <t-dialog :visible.sync="totpVisible" :header="lang.ssetting_text118" :on-close="totpClose" :footer="false"
      width="600">
      <t-form :data="totpFormData" ref="totpDialog" @submit="onTotpSubmit" reset-type="initial">
        <t-form-item :label="lang.setting_text108">
          <span>{{verifyFormData.name}}</span>
        </t-form-item>
        <t-form-item :label="lang.setting_text127">
          <span>
            {{totpInfo.secret}}
          </span>
          <svg class="common-look" @click="handelCopy" style="margin-left: 5px;">
            <use xlink:href="#icon-copy">
            </use>
          </svg>
        </t-form-item>
        <t-form-item label=" ">
          <div class="qrcode-box" style="position: relative;" v-loading="qrcodeLoading">
            <div id="qrcode"></div>
            <div class="common-look" @click="getTotpInfo">{{lang.setting_text129}}</div>
          </div>
        </t-form-item>
        <t-form-item :label="lang.setting_text114" name="code" :rules="[{required:true,message:lang.setting_text128}]">
          <t-input v-model="totpFormData.code" :placeholder="lang.setting_text128" theme="normal" :decimal-places="0">
          </t-input>
        </t-form-item>
        <div class="totp-tip-box">
          <div>{{lang.setting_text130}}</div>
          <div>{{lang.setting_text131}}</div>
        </div>
        <div class="com-f-btn" style="text-align: right;">
          <t-button theme="primary" type="submit" :loading="totpLoading">{{lang.sure}}</t-button>
          <t-button theme="default" variant="base" @click="totpClose">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>

  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/common/qrcode.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/security_center.js"></script>
{include file="footer"}

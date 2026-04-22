{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/login.css" />
<style>
  [v-cloak] {
    display: none !important;
  }
</style>
</head>

<body>
  <div id="mainLoading">
    <div class="ddr ddr1"></div>
    <div class="ddr ddr2"></div>
    <div class="ddr ddr3"></div>
    <div class="ddr ddr4"></div>
    <div class="ddr ddr5"></div>
  </div>
  <div class="template">
    <div id="login" v-cloak>
      <div class="login-container">
        <div class="login-jump-btn">
          <div class="lang-box">
            <span v-for="item in commonData.lang_list" :key="item.display_lang"
              :class="{active: item.display_lang == seletcLang}" @click="changeLang(item.display_lang)">
              {{item.display_name}}
            </span>
          </div>
          <el-button type="primary" class="btn" v-if="commonData.login_register_redirect_show">
            <a :href="commonData.login_register_redirect_url"
              :target="commonData.login_register_redirect_blank ? '_blank' : '_self'">
              {{commonData.login_register_redirect_text}}
            </a>
          </el-button>
        </div>
        <div class="container-back">
          <div class="back-line1"></div>
          <div class="back-line2"></div>
          <div class="back-line3"></div>
          <div class="back-text">
            <div class="text-welcome">WELCOME</div>
            <div class="text-title">
              {{ lang.login_welcome }}{{ commonData.website_name
              }}{{ lang.login_vip }}
            </div>
            <div class="text-level">
              {{ lang.login_level }}
            </div>
          </div>
        </div>
        <div class="container-before">
          <div class="login">
            <!-- <div class="lang-box">
              <el-select style="width: 1.3rem;" v-model="seletcLang" @change="changeLang">
                <el-option v-for="item in commonData.lang_list" :key="item.display_lang" :label="item.display_name"
                  :value="item.display_lang">
                  <div class="lang-option">
                    <img :src="item.display_img" alt="" class="lang-img">
                    <span>{{item.display_name}}</span>
                  </div>
                </el-option>
              </el-select>
            </div> -->
            <div class="login-text">
              <div class="login-text-title">{{ lang.login }}</div>
              <div class="login-text-regist" v-if="commonData.register_email == 1 || commonData.register_phone == 1">
                {{ lang.login_no_account}}
                <a @click="toRegist">{{ lang.login_regist_text }}</a>
              </div>
            </div>
            <div class="login-form" v-show="isLoadingFinish">
              <div class="login-top" v-if="!isShowQrCode">
                <div v-if="commonData.login_email_password == 1 && isPassOrCode" class="login-email"
                  :class="isEmailOrPhone ? 'active' : null" @click="isEmailOrPhone = true">
                  {{ lang.login_email }}
                </div>
                <div v-if="isShowPhoneType" class="login-phone" :class="!isEmailOrPhone? 'active' : null "
                  @click="isEmailOrPhone = false">
                  {{ lang.login_phone }}
                </div>
              </div>
              <div class="form-main">
                <template v-if="!isShowQrCode">
                  <div class="form-item">
                    <el-input v-if="isEmailOrPhone" v-model="formData.email"
                      :placeholder="lang.login_placeholder_pre + lang.login_email">
                    </el-input>
                    <el-input v-else class="input-with-select select-input" v-model="formData.phone"
                      :placeholder="lang.login_placeholder_pre + lang.login_phone">
                      <el-select filterable slot="prepend" v-model="formData.countryCode">
                        <el-option v-for="item in countryList" :key="item.name" :value="item.phone_code"
                          :label="'+' + item.phone_code">
                          +{{item.phone_code}} {{item.name_zh}}
                        </el-option>
                      </el-select>
                    </el-input>
                  </div>
                  <div v-if="isShowPassLogin" class="form-item">
                    <el-input :placeholder="lang.login_pass" v-model="formData.password" type="password"></el-input>
                  </div>
                  <div v-if="isShowCodeLogin" class="form-item code-item">
                    <!-- 邮箱验证码 -->
                    <!-- <template v-if="isEmailOrPhone">
                    <el-input v-model="formData.emailCode" :placeholder="lang.email_code">
                    </el-input>
                    <count-down-button ref="emailCodebtn" @click.native="sendEmailCode" my-class="code-btn">
                    </count-down-button>
                  </template> -->
                    <!-- 手机验证码 -->
                    <el-input v-model="formData.phoneCode" :placeholder="lang.login_phone_code"></el-input>
                    <count-down-button ref="phoneCodebtn" @click.native="sendPhoneCode" my-class="code-btn">
                    </count-down-button>
                  </div>
                </template>
                <template v-if="isShowQrCode">
                  <div class="qr-box" v-if="!isShowWxSelectAccount">
                    <div class="qr-box-img" v-loading="qrLoading">
                      <img @click="handelRefreshQrCode" v-if="qrCodeData.img_url" :src="qrCodeData.img_url" alt="">
                      <div class="qr-expire-time" v-if="qrCodeData.is_refresh" @click="getQrcode">
                        <i class="el-icon-refresh-right"></i>
                        <span>{{lang.login_text12 }}</span>
                      </div>
                    </div>
                    <div class="qr-box-tips">{{ lang.login_text11 }}</div>
                  </div>
                  <div class="qr-box-select-account" v-if="isShowWxSelectAccount">
                    <div class="qr-box-back" @click="handelBackQr">
                      <i class="el-icon-back"></i>
                      {{lang.status_text2}}
                    </div>
                    <div class="qr-box-select-account-title">
                      {{ lang.login_text13 }}
                    </div>
                    <el-radio-group v-model="selectClient" style="display: inline-flex;flex-wrap: wrap;row-gap: 15px;">
                      <el-radio :disabled="item.status !== 1" v-for="item in clientList" :key="item.id"
                        :label="item.id">
                        {{item.username}}
                      </el-radio>
                    </el-radio-group>
                    <el-button type="primary" style="width: 100%;" :loading="selectClientLoading" class="login-btn"
                      @click="handleSelectClientLogin">{{ lang.login_text14 }}
                    </el-button>
                  </div>
                </template>
                <div class="form-item rember-item" v-show="!isShowQrCode">
                  <!-- 1-31 取消原有的记住密码 -->
                  <el-checkbox v-model="checked"></el-checkbox>
                  <span class="read-text" @click="checked = !checked"> {{ lang.login_read}}
                    <a @click="goHelpUrl('terms_service_url')">{{lang.read_service}}</a>
                    {{ lang.read_and}}<a @click="goHelpUrl('terms_privacy_url')">{{ lang.read_privacy}}</a>
                  </span>
                  <span>
                    <a @click="toForget">{{ lang.login_forget }}</a>
                  </span>
                </div>
                <div class="read-item" v-if="errorText.length !== 0 && !isShowQrCode">
                  <el-alert :title="errorText" type="error" show-icon :closable="false"></el-alert>
                </div>
                <div class="form-item" v-if="!isShowQrCode">
                  <el-button type="primary" :loading="loginLoading" class="login-btn" @click="doLogin">{{ lang.login }}
                  </el-button>
                  <!-- 登录方式切换 -->
                  <template v-if="isShowChangeTpyeBtn">
                    <el-button v-if="isPassOrCode " class="pass-btn"
                      @click="changeLoginType">{{ lang.login_code_login }}
                    </el-button>
                    <el-button v-else class="pass-btn" @click="changeLoginType">{{ lang.login_pass_login }}
                    </el-button>
                  </template>
                </div>
                <!-- 三方登录 -->
                <template v-if="commonData.oauth && commonData.oauth?.length > 0">
                  <div class="form-item line-item">
                    <el-divider><span class="text">or</span></el-divider>
                  </div>
                  <div class="form-item login-type">
                    <div class="oauth-item" v-for="(item,index) in commonData.oauth" :key="index"
                      @click="oauthLogin(item)">
                      <img :src="item.img" alt="" class="oauth-img" />
                    </div>
                  </div>
                </template>
              </div>
            </div>
            <div class="qr-code" @click="handleQrCode" v-if="isShowWxScanLogin">
              <template v-if="!isShowQrCode">
                <svg t="1745561758296" class="login-icon" viewBox="0 0 1024 1024" version="1.1"
                  xmlns="http://www.w3.org/2000/svg" p-id="11650" width="48" height="48">
                  <path d="M92.16 92.16h276.48v275.626667h-0.853333L460.8 460.8V0H0l92.16 92.16z" p-id="11651"
                    fill="var(--color-primary)"></path>
                  <path
                    d="M294.4 166.4H166.4l128 128zM1024 0H563.2v460.8h460.8V0z m-92.16 367.786667h-276.48V92.16h276.48v275.626667z"
                    p-id="11652" fill="var(--color-primary)"></path>
                  <path
                    d="M729.6 166.4h128v128h-128zM1024 1024v-88.746667h-88.746667L1024 1024zM834.986667 756.053333v-100.266666H942.933333V563.2h-183.893333v96.853333h-98.986667l96 96zM655.36 563.2H563.2l92.16 92.16z"
                    p-id="11653" fill="var(--color-primary)"></path>
                  <path
                    d="M1024 834.133333v-178.346666h-81.066667v100.266666h-107.946666v78.933334l79.786666 79.786666v-80.64z"
                    p-id="11654" fill="var(--color-primary)"></path>
                </svg>
              </template>
              <template v-if="isShowQrCode">
                <svg t="1745563177451" class="icon" viewBox="0 0 1141 1024" version="1.1"
                  xmlns="http://www.w3.org/2000/svg" p-id="15106" width="48" height="48">
                  <path
                    d="M850.148514 1023.73048L569.750004 745.642967h504.595118V66.004155H66.909883v181.100683L0 180.808954V62.493161c0-16.617971 6.669988-32.474943 18.519968-44.235923A63.19489 63.19489 0 0 1 63.13689 0.00027h1014.748226c16.763971 0 32.767943 6.494989 44.616922 18.256968 11.848979 11.731979 18.519968 27.617952 18.519967 44.235923V749.38896c0 34.52394-28.37995 62.492891-63.136889 62.492891h-280.779509v118.373793h154.12573a47.249917 47.249917 0 0 1 41.456928 23.229959c8.572985 14.628974 8.572985 32.621943 0 47.249918s-24.458957 23.493959-41.456928 23.259959H850.148514v-0.264z"
                    fill="var(--color-primary)" p-id="15107"></path>
                  <path d="M455.559204 672.764094l-280.017511-304.769467V175.541963h760.68067v497.369131H455.560204z"
                    fill="var(--color-primary)" p-id="15108"></path>
                </svg>
              </template>
            </div>
          </div>
        </div>
      </div>
      <!-- 安全验证 -->
      <security-verification ref="securityRef" @confirm="hadelSecurityConfirm"
        action-type="exception_login"></security-verification>
      <!-- 验证码 -->
      <captcha-dialog :is-show-captcha="isShowCaptcha" captcha-id="login-captcha" @get-captcha-data="getData"
        @captcha-cancel="captchaCancel" ref="captcha">
      </captcha-dialog>


    </div>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/components/captchaDialog/captchaDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/countDownButton/countDownButton.js"></script>
  <script
    src="/{$template_catalog}/template/{$themes}/components/securityVerification/securityVerification.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/crypto-js.min.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/common/jquery.mini.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/login.js"></script>
  {include file="footer"}

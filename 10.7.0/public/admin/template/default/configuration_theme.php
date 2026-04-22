{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="configuration-theme" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="box">
        <t-form :data="formData" :rules="rules" label-align="top" :label-width="80" ref="formValidatorStatus"
          @submit="onSubmit">
          <t-tabs v-model="value" @change="changeTab" class="top-tabs" placement="left" :class="{'other-panel': value !== 'clientarea_theme'}">
            <t-tab-panel :label="lang.auth_all" disabled class="com-first-title"></t-tab-panel>
            <t-tab-panel value="clientarea_theme" :label="lang.member_center">
              <t-tabs v-model="clientarea_type" class="chiled-tabs" @change="changeClient">
                <t-radio-group variant="primary-filled" size="large" v-model="clinetarea_switch" class="top-radio">
                  <t-radio-button value="pc">PC</t-radio-button>
                  <t-radio-button value="mobile">{{lang.finance_search_text7}}</t-radio-button>
                </t-radio-group>
                <t-tab-panel value="global_theme" :label="lang.global_theme">
                  <div v-show="clinetarea_switch === 'pc'">
                    <t-form-item name="lang_admin">
                      <ul class="theme-box">
                        <li class="item" v-for="item in formData.clientarea_theme_list" :key="item.name"
                          :class="{active: item.name === formData.clientarea_theme}"
                          @click="selectTheme('clientarea_theme',item.name)">
                          <div class="icon">
                            <t-icon name="check"></t-icon>
                          </div>
                          <div class="img">
                            <img :src="item.img" alt="">
                          </div>
                          <p class="text third-text-color">{{item.name}}</p>
                        </li>
                      </ul>
                    </t-form-item>
                  </div>
                  <div v-show="clinetarea_switch === 'mobile'">
                    <div class="sub-title">
                      <span>{{lang.member_m_center}}</span>
                      <t-switch v-model="formData.clientarea_theme_mobile_switch " size="large" class="com-gap" :custom-value="['1','0']"></t-switch>
                    </div>
                    <t-form-item v-show="formData.clientarea_theme_mobile_switch  === '1'">
                      <ul class="theme-box">
                        <li class="item" v-for="item in formData.clientarea_theme_mobile_list" :key="item.name"
                          :class="{active: item.name === formData.clientarea_theme_mobile}"
                          @click="selectTheme('clientarea_theme_mobile',item.name)">
                          <div class="icon">
                            <t-icon name="check"></t-icon>
                          </div>
                          <div class="img">
                            <img :src="item.img" alt="">
                          </div>
                          <p class="text third-text-color">{{item.name}}</p>
                        </li>
                      </ul>
                    </t-form-item>
                  </div>
                </t-tab-panel>
                <t-tab-panel value="home_theme" :label="lang.home_theme">
                  <div v-show="clinetarea_switch === 'pc'">
                    <t-form-item name="home_theme">
                      <ul class="theme-box">
                        <li class="item" v-for="item in formData.home_theme_list" :key="item.name"
                          :class="{active: item.name === formData.home_theme}"
                          @click="selectTheme('home_theme',item.name)">
                          <div class="icon">
                            <t-icon name="check"></t-icon>
                          </div>
                          <div class="img">
                            <img :src="item.img" alt="">
                          </div>
                          <p class="text third-text-color">
                            {{item.name}}
                            <span @click="handleThemeConfig(item.name)" v-if="item.name === 'mh301'">{{lang.theme_text4}}</span>
                          </p>
                        </li>
                      </ul>
                    </t-form-item>
                  </div>
                  <div v-show="clinetarea_switch === 'mobile'">
                    <t-form-item name="home_theme_mobile" :label="lang.home_mobile_theme">
                      <ul class="theme-box">
                        <li class="item" v-for="item in formData.home_theme_mobile_list" :key="item.name"
                          :class="{active: item.name === formData.home_theme_mobile}"
                          @click="selectTheme('home_theme_mobile',item.name)">
                          <div class="icon">
                            <t-icon name="check"></t-icon>
                          </div>
                          <div class="img">
                            <img :src="item.img" alt="">
                          </div>
                          <p class="text third-text-color">{{item.name}}</p>
                        </li>
                      </ul>
                    </t-form-item>
                  </div>
                </t-tab-panel>
                <t-tab-panel value="cart_theme" :label="lang.cart_theme">
                  <div v-show="clinetarea_switch === 'pc'">
                    <t-form-item name="cart_admin">
                      <ul class="theme-box">
                        <li class="item" v-for="item in formData.cart_theme_list" :key="item.name"
                          :class="{active: item.name === formData.cart_theme}"
                          @click="selectTheme('cart_theme',item.name)">
                          <div class="icon">
                            <t-icon name="check"></t-icon>
                          </div>
                          <div class="img">
                            <img :src="item.img" alt="">
                          </div>
                          <p class="text third-text-color">{{item.name}}</p>
                        </li>
                      </ul>
                    </t-form-item>
                  </div>
                  <div v-show="clinetarea_switch === 'mobile'">
                    <div class="sub-title">
                      <span>{{lang.cart_theme_mobile}}</span>
                      <t-switch v-model="formData.clientarea_theme_mobile_switch" class="com-gap" size="large" :custom-value="['1','0']"></t-switch>
                    </div>
                    <t-form-item name="cart_admin" v-show="formData.clientarea_theme_mobile_switch  === '1'">
                      <ul class="theme-box">
                        <li class="item" v-for="item in formData.cart_theme_mobile_list" :key="item.name"
                          :class="{active: item.name === formData.cart_theme_mobile}"
                          @click="selectTheme('cart_theme_mobile',item.name)">
                          <div class="icon">
                            <t-icon name="check"></t-icon>
                          </div>
                          <div class="img">
                            <img :src="item.img" alt="">
                          </div>
                          <p class="text third-text-color">{{item.name}}</p>
                        </li>
                      </ul>
                    </t-form-item>
                  </div>
                  <div style="display: flex; column-gap: 20px; margin-top: 30px; flex-wrap: wrap;">
                    <t-form-item name="first_navigation" :label="lang.nav_text16">
                      <t-input style="width: 300px;" v-model="formData.first_navigation" :placeholder="lang.nav_text16">
                      </t-input>
                    </t-form-item>
                    <t-form-item name="second_navigation" :label="lang.nav_text17">
                      <t-input style="width: 300px;" v-model="formData.second_navigation"
                        :placeholder="lang.nav_text17">
                      </t-input>
                    </t-form-item>
                  </div>
                  <div>
                    <t-form-item name="cart_instruction">
                      <template #label>
                        <span>{{lang.nav_text18}}</span>
                        <t-switch size="large" v-model="formData.cart_instruction" :custom-value="[1,0]"
                          @change="changeCartInstruction"></t-switch>
                      </template>
                      <com-tinymce style="margin-top: 10px;" v-if="formData.cart_instruction == 1" ref="comTinymce"
                        id="cart_instruction">
                      </com-tinymce>
                    </t-form-item>
                  </div>
                  <div style="margin-top: 10px;">
                    <t-form-item name="cart_change_product" :label="lang.nav_text17">
                      <template #label>
                        <span>{{lang.nav_text19}}</span>
                        <t-switch size="large" v-model="formData.cart_change_product" :custom-value="[1,0]"></t-switch>
                      </template>
                    </t-form-item>
                  </div>
                </t-tab-panel>
                <t-tab-panel value="product_theme" :label="lang.product_theme">
                  <t-form-item :label="lang.host_model">
                    <t-select v-model="host_model">
                      <t-option v-for="item in formData.module_list" :key="item.name" :value="item.name"
                        :label="item.display_name">
                      </t-option>
                    </t-select>
                  </t-form-item>

                  <div v-show="clinetarea_switch === 'pc'">
                    <t-form-item name="home_theme" :label="lang.product_theme">
                      <ul class="theme-box">
                        <li class="item" v-for="item in getHostModelList(host_model, 'theme_list')" :key="item.name"
                          :class="{active: item.name === formData.home_host_theme[host_model]}"
                          @click="selectTheme('home_host_theme', item.name,host_model)">
                          <div class="icon">
                            <t-icon name="check"></t-icon>
                          </div>
                          <div class="img">
                            <img :src="item.img" alt="">
                          </div>
                          <p class="text third-text-color">{{item.name}}</p>
                        </li>
                      </ul>
                    </t-form-item>
                  </div>
                  <div v-show="clinetarea_switch === 'mobile'">
                    <t-form-item name="home_theme_mobile" :label="lang.setting_text12 + lang.product_theme">
                      <ul class="theme-box">
                        <li class="item" v-for="item in getHostModelList(host_model, 'theme_mobile_list')"
                          :key="item.name" :class="{active: item.name === formData.home_host_theme_mobile[host_model]}"
                          @click="selectTheme('home_host_theme_mobile',item.name,host_model)">
                          <div class="icon">
                            <t-icon name="check"></t-icon>
                          </div>
                          <div class="img">
                            <img :src="item.img" alt="">
                          </div>
                          <p class="text third-text-color">{{item.name}}</p>
                        </li>
                      </ul>
                    </t-form-item>
                  </div>
                </t-tab-panel>
              </t-tabs>
            </t-tab-panel>
            <t-tab-panel value="web_switch" :label="lang.official_theme">
              <div class="mb32 flex">
                <span class="com-first-title">{{lang.official_theme}}&nbsp;&nbsp;</span>
                <t-switch v-model="formData.web_switch" :custom-value="['1','0']" size="large"></t-switch>
              </div>
              <t-form-item v-show="formData.web_switch === '1'">
                <ul class="theme-box">
                  <li class="item" v-for="item in formData.web_theme_list" :key="item.name"
                    :class="{active: item.name === formData.web_theme}" @click="selectTheme('web_theme',item.name)">
                    <div class="icon">
                      <t-icon name="check"></t-icon>
                    </div>
                    <div class="img">
                      <img :src="item.img" alt="">
                      <t-button @click="jumpController(item)" class="jump_controller">
                        {{lang.theme_controller}}
                        <t-tooltip :content="lang.theme_controller_tip" overlay-class-name="theme_controller_tip"
                          :show-arrow="false" theme="light" placement="top-left">
                          <t-icon name="help-circle"></t-icon>
                        </t-tooltip>
                      </t-button>
                    </div>
                    <p class="text third-text-color">{{item.name}}</p>
                  </li>
                </ul>
              </t-form-item>
            </t-tab-panel>
            <t-tab-panel value="admin_theme" :label="lang.back_manage">
              <t-form-item :label="lang.back_manage">
                <template slot="label">
                  <span class="com-first-title mb32 flex">{{lang.back_manage}}</span>
                </template>
                <ul class="theme-box">
                  <li class="item" v-for="item in formData.admin_theme_list" :key="item.name"
                    :class="{active: item.name === formData.admin_theme}" @click="selectTheme('admin_theme',item.name)">
                    <div class="icon">
                      <t-icon name="check"></t-icon>
                    </div>
                    <div class="img">
                      <img :src="item.img" alt="">
                    </div>
                    <p class="text third-text-color">{{item.name}}</p>
                  </li>
                </ul>
              </t-form-item>
            </t-tab-panel>
            <t-form-item class="com-is-fixed" :class="{'has-shadow': !footerInView}">
              <t-button theme="primary" type="submit" :loading="submitLoading"
                v-permission="'auth_system_configuration_system_configuration_theme_configuration_save_configuration'">{{lang.hold}}</t-button>
            </t-form-item>
          </t-tabs>
        </t-form>
      </div>
      <!-- 主题配置弹窗 -->
      <t-dialog :header="lang.theme_text4" :visible.sync="themeConfigVisible" 
        width="1200px" class="theme-config-dialog" :footer="false" placement="center">
        <div class="top-area">
          <h3 class="small-tit">{{lang.theme_text5}}</h3>
          <div class="area-item">
            <span>{{lang.theme_text6}}：</span>
            <t-radio-group v-model="currentThemeConfig.display_one" @change="saveSpecifyTheme">
              <t-radio v-for="item in currentThemeConfig.display_one_options" :value="item.value" :key="item.value">{{item.name}}</t-radio>
            </t-radio-group>
          </div>
           <div class="area-item">
            <span>{{lang.theme_text7}}：</span>
            <t-radio-group v-model="currentThemeConfig.display" @change="saveSpecifyTheme">
              <t-radio v-for="item in currentThemeConfig.display_options" :value="item.value" :key="item.value">{{item.name}}</t-radio>
            </t-radio-group>
          </div>
        </div>
        <!-- banner -->
        <div class="banner-table">
           <h3 class="small-tit">{{lang.theme_text8}}</h3>
          <div class="top">
            <div class="interval">
              {{lang.theme_text9}}
              <t-input-number v-model="currentThemeConfig.display_time" theme="normal" 
                :decimal-places="0"
                :placeholder="lang.input" :suffix="lang.seconds" :min="0" 
                @blur="saveSpecifyTheme($event, 'time')"
                @enter="saveSpecifyTheme($event, 'time')"
                >
              </t-input-number>
              {{lang.theme_text10}}
            </div>
            <t-button @click="addBanner">{{lang.order_text53}}</t-button>
          </div>
          <t-table row-key="id" :columns="bannerColumns" :data="tempBanner" :loading="loading" drag-sort="row-handler"
            @drag-sort="onDragSort">
            <template #drag="{row}">
              <t-icon name="move"></t-icon>
            </template>
            <template #img="{row}">
              <img :src="row.img" alt="" class="b-img" v-if="!row.edit" />
              <t-upload v-model="editItem.img" :action="uploadUrl" :headers="uploadHeaders"
                :placeholder="lang.upload_tip" theme="image" accept="image/*" :auto-upload="true" @drop="onDrop"
                :format-response="formatResponse" :allow-upload-duplicate-file="false" v-else>
              </t-upload>
            </template>
            <template #url="{row}">
              <span v-if="!row.edit">{{row.url}}</span>
              <t-input v-else v-model="editItem.url" :placeholder="lang.jump_link"></t-input>
            </template>
            <template #time="{row}">
              <template v-if="!row.edit">
                {{moment(row.start_time *
                  1000).format('YYYY-MM-DD')}}&nbsp;{{lang.view_to}}&nbsp;{{moment(row.end_time *
                  1000).format('YYYY-MM-DD')}}
              </template>
              <t-date-range-picker allow-input clearable v-else v-model="editItem.timeRange" format="YYYY-MM-DD" />
            </template>
            <template #show="{row}">
              <t-switch v-model="row.show" :custom-value="[1,0]" @change="changeShow($event,row)"></t-switch>
            </template>
            <template #notes="{row}">
              <span v-if="!row.edit">{{row.notes}}</span>
              <t-input v-else v-model="editItem.notes" :placeholder="lang.notes"></t-input>
            </template>
            <template #op="{row, rowIndex}">
              <template v-if="row.edit">
                <t-tooltip :content="lang.cancel" :show-arrow="false" theme="light">
                  <t-icon name="close" class="common-look" @click="cancelItem(row, rowIndex)"></t-icon>
                </t-tooltip>
                <t-tooltip :content="lang.hold" :show-arrow="false" theme="light">
                  <t-icon name="save" class="common-look" @click="saveItem(row,rowIndex)"></t-icon>
                </t-tooltip>
              </template>
              <template v-else>
                <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
                  <t-icon name="edit" size="18px" @click="handlerEdit(row)" class="common-look"></t-icon>
                </t-tooltip>
                <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
                  <t-icon name="delete" class="common-look" @click="delteItem(row)"></t-icon>
                </t-tooltip>
              </template>
            </template>
          </t-table>
        </div>
         <div class="com-f-btn">
          <t-button theme="default" variant="base" @click="themeConfigVisible=false">{{lang.cancel}}</t-button>
        </div>
      </t-dialog>
      <!-- 删除弹窗 -->
      <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
        <template slot="footer">
          <t-button theme="primary" @click="sureDel" :loading="submitLoading">{{lang.sure}}</t-button>
          <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
        </template>
      </t-dialog>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/tinymce/tinymce.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/comTinymce/comTinymce.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/configuration_theme.js"></script>
{include file="footer"}

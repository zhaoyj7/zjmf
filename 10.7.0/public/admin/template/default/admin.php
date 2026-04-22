{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/setting.css">
<div id="content" class="admin table" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li class="active" v-permission="'auth_system_configuration_admin_management_view'">
          <a href="javascript:;">{{lang.admin_setting}}</a>
        </li>
        <li v-permission="'auth_system_configuration_admin_group_view'">
          <a href="admin_role.htm">{{lang.group_setting}}</a>
        </li>
      </ul>
      <div class="common-header">
        <div>
          <t-button @click="addUser" class="add"
            v-permission="'auth_system_configuration_admin_management_create_admin'">{{lang.add}}</t-button>
        </div>
        <div class="right-search">
          <t-select v-model="params.status" :placeholder="`${lang.admin}${lang.status}`" clearable>
            <t-option v-for="item in adminStatus" :value="item.value" :label="item.label" :key="item.value">
            </t-option>
          </t-select>
          <div class="com-search">
            <t-input v-model="params.keywords" class="search-input"
              :placeholder="`ID、${lang.nickname}、${lang.username}、${lang.email}`" @keypress.enter.native="search"
              :on-clear="clearKey" clearable>
            </t-input>
          </div>
          <t-button @click="search">{{lang.query}}</t-button>
        </div>
      </div>
      <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading"
        :table-layout="tableLayout ? 'auto' : 'fixed'" @sort-change="sortChange" :hide-sort-tips="hideSortTips">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>

        <template #name="{row}">
          <div style="display: flex;align-items: center; gap: 5px;">
            <span>{{row.name}}</span>
            <t-tooltip v-if="row.totp_bind === 1" :content="lang.setting_text137" :show-arrow="false" theme="light">
              <svg t="1745372995785" class="icon" viewBox="0 0 1024 1024" version="1.1"
                xmlns="http://www.w3.org/2000/svg" p-id="8928" width="16" height="16">
                <path
                  d="M168 83.2L438.4 8c46.4-11.2 97.6-11.2 148.8 0l270.4 75.2c51.2 11.2 86.4 57.6 86.4 108.8v315.2c0 206.4-126.4 395.2-321.6 470.4L512 1024l-108.8-46.4C208 904 81.6 713.6 81.6 507.2V192c0-51.2 35.2-97.6 86.4-108.8zM512 937.6l80-33.6c161.6-62.4 270.4-220.8 270.4-395.2V192c0-12.8-8-27.2-24-30.4-1.6 0-3.2 0-3.2-1.6L568 86.4C547.2 81.6 528 80 510.4 80s-35.2 1.6-52.8 6.4L188.8 160c-1.6 0-3.2 0-3.2 1.6-16 3.2-24 17.6-24 30.4v315.2c0 174.4 108.8 334.4 270.4 395.2 1.6 0 1.6 0 3.2 1.6l76.8 33.6z m152-406.4c16-16 43.2-16 59.2 0s16 43.2 0 59.2l-136 136c-16 16-43.2 16-59.2 0s-16-43.2 0-59.2l136-136z m-54.4-174.4c16-16 43.2-16 59.2 0s16 43.2 0 59.2L419.2 667.2c-16 16-43.2 16-59.2 0s-16-43.2 0-59.2l249.6-251.2z m-174.4-60.8c16-16 43.2-16 59.2 0s16 43.2 0 59.2l-136 131.2c-16 16-43.2 16-59.2 0s-16-43.2 0-59.2l136-131.2z"
                  fill="#2ba471" p-id="8929"></path>
              </svg>
            </t-tooltip>
          </div>
        </template>

        <template #phone="{row}">
          <span v-if="row.phone">+{{row.phone_code}}&nbsp;-&nbsp;{{row.phone}}</span>
        </template>
        <template #status="{row}">
          <t-tag theme="danger" class="status" v-if="row.status == 0" variant="light">{{lang.disable}}</t-tag>
          <t-tag v-else-if="row.lock === 1" theme="warning" variant="light">{{lang.setting_text138}}</t-tag>
          <t-tag theme="primary" class="status" v-else variant="light">{{lang.enable}}</t-tag>
        </template>
        <template #op="{row}">
          <t-tooltip :content="lang.setting_text139" :show-arrow="false" theme="light" v-if="row.lock === 1">
            <t-icon name="lock-off" class="common-look" @click="handleUnlock(row)"
              v-permission="'auth_system_configuration_admin_management_deactivate_enable_admin'"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.setting_text134" :show-arrow="false" theme="light" v-if="row.totp_bind == 1 ">
            <t-icon name="link-unlink" class="common-look" @click="handleUnbindTotp(row)"
              v-permission="'auth_system_configuration_admin_management_deactivate_enable_admin'"></t-icon>
          </t-tooltip>

          <t-tooltip :content="row.status ? lang.disable : lang.enable" :show-arrow="false" theme="light">
            <t-icon :name="row.status ? 'minus-circle' : 'play-circle-stroke'" class="common-look"
              @click="changeStatus(row)" :class="{disable: row.id===1, rotate: row.status}"
              v-permission="'auth_system_configuration_admin_management_deactivate_enable_admin'">
            </t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.update" :show-arrow="false" theme="light">
            <t-icon name="edit-1" class="common-look" @click="updateAdmin(row)"
              v-permission="'auth_system_configuration_admin_management_update_admin'">
            </t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
            <t-icon name="delete" class="common-look" @click="deleteUser(row)" :class="{disable: row.id===1}"
              v-permission="'auth_system_configuration_admin_management_delete_admin'">
            </t-icon>
          </t-tooltip>
        </template>
      </t-table>
      <com-pagination v-if="total" :total="total"
        :page="params.page" :limit="params.limit"
        @page-change="changePage">
      </com-pagination>
    </t-card>

    <!-- 添加管理员 -->
    <t-dialog :visible.sync="visible" :header="addTip" :on-close="close" :footer="false" width="600"
      class="admin-dialog">
      <t-form :rules="rules" :data="formData" ref="userDialog" @submit="onSubmit">
        <t-form-item :label="lang.username" name="name">
          <t-input :placeholder="lang.name" v-model="formData.name" />
        </t-form-item>
        <t-form-item :label="lang.password" name="password" :rules="[
          { required: optType === 'create' ? true : false , message: lang.input + lang.password, type: 'error' },
          { pattern: /^[\w@!#$%^&*()+-_]{6,32}$/, message: lang.verify8 + '，' + lang.verify14 + '6~32', type: 'warning' }
        ]">
          <t-input :placeholder="lang.password" :type="formData.password ? 'password' : 'text'" autocomplete="off"
            v-model="formData.password" />
        </t-form-item>
        <t-form-item :label="lang.surePassword" name="repassword" :rules="[
      { required:  optType === 'create' ? true : false, message: lang.input + lang.surePassword, type: 'error' },
      { validator: checkPwd, trigger: 'blur' }
    ]">
          <t-input :placeholder="lang.surePassword" :type="formData.repassword ? 'password' : 'text'" autocomplete="off"
            v-model="formData.repassword" />
        </t-form-item>
        <t-form-item :label="lang.phone" name="phone">
          <t-select v-model="formData.phone_code" filterable style="width: 100px" :placeholder="lang.phone_code">
            <t-option v-for="item in country" :value="item.phone_code" :label="item.name_zh + '+' + item.phone_code"
              :key="item.name">
            </t-option>
          </t-select>
          <t-input :placeholder="lang.phone" v-model="formData.phone" style="width: calc(100% - 100px);" />
        </t-form-item>
        <t-form-item :label="lang.email" name="email">
          <t-input :placeholder="lang.email" v-model="formData.email" />
        </t-form-item>
        <t-form-item :label="lang.nickname" name="nickname">
          <t-input :placeholder="lang.nickname" v-model="formData.nickname" />
        </t-form-item>
        <t-form-item :label="lang.group" name="role_id">
          <t-select v-model="formData.role_id" :placeholder="lang.group" :disabled="formData.id===1"
            :popup-props="popupProps">
            <t-option v-for="item in roleList" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <div class="f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="close">{{lang.cancel}}</t-button>
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

    <!-- 启用/停用 -->
    <t-dialog theme="warning" :header="statusTip" :visible.sync="statusVisble">
      <template slot="footer">
        <t-button theme="primary" @click="sureChange" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="closeDialog">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>

    <!-- 解绑TOTP -->
    <t-dialog theme="warning" :header="lang.setting_text140" :visible.sync="unbindTotpVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureUnbindTotp" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="unbindTotpVisible = false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>


    <!-- 解锁账户 -->

    <t-dialog theme="warning" :header="lang.setting_text141" :visible.sync="unlockVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureUnlock" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="unlockVisible = false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>


  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/setting.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/admin.js"></script>
{include file="footer"}

{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/product.css">
<div id="content" class="hasCrumb" v-cloak>
  <com-config>
    <!-- crumb -->
    <div class="com-crumb">
      <span>{{lang.refund_commodit_management}}</span>
      <t-icon name="chevron-right"></t-icon>
      <a href="product.htm">{{lang.product_list}}</a>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.product_selef_text1}}</span>
    </div>
    <t-card class="product-container com-sidebar-box">
      <div class="left-box">
        <ul class="common-tab">
          <li class="all">{{lang.auth_all}}</li>
          <li v-permission="'auth_product_detail_basic_info_view'">
            <a :href="`product_detail.htm?id=${id}`">{{lang.basic_info}}</a>
          </li>
          <li v-permission="'auth_product_detail_server_view'">
            <a :href="`product_api.htm?id=${id}`">{{lang.interface_manage}}</a>
          </li>
          <li class="active" v-permission="'auth_product_detail_custom_field_view'">
            <a>{{lang.product_selef_text1}}</a>
          </li>
          <li v-permission="'auth_product_detail_custom_host_name'">
            <a :href="`product_custom_name.htm?id=${id}`">{{lang.product_custom_name}}</a>
          </li>
        </ul>
      </div>
      <div class="product-self box">
        <div class="material-top">
          <div>{{lang.product_selef_text2}}</div>
          <t-button @click="handADDmaterial" :disabled="isAgent" v-permission="'auth_product_detail_custom_field_create_field'">{{lang.client_custom_label3}}</t-button>
        </div>
        <t-table hover row-key="id" :loading="loading" :data="list" :columns="columns" drag-sort="row-handler"
         @drag-sort="onDragSort">
          <template #drag="{row}">
            <t-icon name="move" style="cursor: move;" v-if="!row.is_global"></t-icon>
          </template>
          <template #field_name="{row}">
            <span>
              <span class="com-red" v-if="row.is_required === 1">*</span>
              <span class="com-red" v-if="row.is_global === 1">({{lang.product_set_text133}})</span>{{row.field_name}}
            </span>
          </template>
          <template #field_type="{row}">
            <t-tooltip :content="row.field_option" v-if="row.field_type === 'dropdown'" theme="light">
              <span>{{calcTypeText(row)}}</span>
            </t-tooltip>
            <span v-if="row.field_type !== 'dropdown'">{{calcTypeText(row)}}</span>
          </template>
          <template #description="{row}">
            <span>{{row.description || '--'}}</span>
          </template>
          <template #regexpr="{row}">
            <span>{{row.regexpr || '--'}}</span>
          </template>
          <template #watch="{row}">
            <span>
              {{ calcWatch(row) || '--' }}
            </span>
          </template>
          <template #op="slotProps">
            <t-icon name="edit-1" color="var(--td-brand-color)" style="margin-right: 10px;"
              @click="edit(slotProps.row)" class="common-look" :class="{'server-disabled': isAgent}"
              v-permission="'auth_product_detail_custom_field_update_field'" v-if="!slotProps.row.is_global">
            </t-icon>
            <t-icon name="delete" color="var(--td-brand-color)" class="common-look" :class="{'server-disabled': isAgent}"
              @click="deletes(slotProps.row.id)" v-permission="'auth_product_detail_custom_field_delete_field'" v-if="!slotProps.row.is_global"></t-icon>
          </template>
        </t-table>
      </div>
    </t-card>

    <!-- 删除提示框 -->
    <t-dialog theme="warning" :header="lang.client_custom_label6" :close-btn="false" :visible.sync="delVisible" class="delDialog">
      <template slot="footer">
        <t-button theme="primary" @click="sureDelUser" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>

    <!-- 创建字段弹窗 -->
    <t-dialog :header="editId ? lang.client_custom_label7 : lang.client_custom_label8" :visible.sync="materialVisble" :footer="false" width="650" @closed="materialDiaClose">
      <t-form :rules="rules" ref="userDialog" label-align="top" :data="formData" @submit="onSubmit" :label-width="120" reset-type="initial">
        <t-form-item name="field_name">
          <template slot="label">
            <span class="form-flex-label">
              <span>{{lang.client_custom_label9}}</span>
              <t-checkbox v-model="formData.is_required" v-if="formData.field_type !== 'explain'">{{lang.client_custom_label21}}</t-checkbox>
            </span>
          </template>
          <t-input v-model="formData.field_name" :placeholder="`${lang.client_custom_label9}${multiliTip}`"></t-input>
        </t-form-item>
        <t-form-item :label="lang.client_custom_label10" name="field_type">
          <t-select v-model="formData.field_type" :placeholder="lang.client_custom_label10" :disabled="editId !== null">
            <t-option v-for="ele in typeList" :value="ele.value" :label="ele.label" :key="ele.value">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.client_custom_label12" :help="lang.client_custom_label11" name="field_option" v-if="formData.field_type === 'dropdown'">
          <t-input v-model="formData.field_option" :placeholder="lang.client_custom_label11"></t-input>
        </t-form-item>
        <t-form-item :label="lang.client_custom_label40" name="explain_content"
          v-if="formData.field_type === 'explain'">
          <t-textarea v-model="formData.explain_content" :placeholder="`${lang.client_custom_label41}${multiliTip}`"
            :autosize="{ minRows: 5, maxRows: 10 }">
          </t-textarea>
        </t-form-item>
        <template v-if="formData.field_type !== 'explain'">
          <t-form-item :label="lang.client_custom_label23">
            <t-input v-model="formData.description" :placeholder="`${lang.client_custom_label23}${multiliTip}`"></t-input>
          </t-form-item>
          <t-form-item :label="lang.client_custom_label24" :help="lang.client_custom_label25">
            <t-input v-model="formData.regexpr" :placeholder="lang.client_custom_label33"></t-input>
          </t-form-item>
        </template>
        <t-form-item :label="lang.client_custom_label30">
          <div style="display: flex; flex-wrap: wrap; column-gap: 10px; row-gap: 10px;">
            <t-checkbox v-model="formData.show_order_page" :disabled="formData.field_type === 'explain'">{{lang.client_custom_label26}}</t-checkbox>
            <template v-if="formData.field_type !== 'explain'">
              <t-checkbox v-model="formData.show_order_detail" v-if="formData.field_type !=='password' && formData.field_type !=='link'">{{lang.client_custom_label27}}</t-checkbox>
              <t-checkbox v-model="formData.show_client_host_detail">{{lang.client_custom_label28}}</t-checkbox>
              <t-checkbox v-model="formData.show_admin_host_detail">{{lang.client_custom_label29}}</t-checkbox>
              <t-checkbox v-model="formData.show_client_host_list">{{lang.client_custom_label34}}</t-checkbox>
              <t-checkbox v-model="formData.show_admin_host_list">{{lang.client_custom_label38}}</t-checkbox>
            </template>
          </div>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.client_custom_label13}}</t-button>
          <t-button theme="default" variant="base" @click="materialVisble=false">{{lang.client_custom_label14}}</t-button>
        </div>
      </t-form>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/product_self_field.js"></script>
{include file="footer"}

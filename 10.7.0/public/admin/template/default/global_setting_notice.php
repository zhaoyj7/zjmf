{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/product.css">
<div id="content" class="product-notice-setting hasCrumb" v-cloak>
  <com-config>
    <div class="com-crumb">
      <span>{{lang.refund_commodit_management}}</span>
      <t-icon name="chevron-right"></t-icon>
      <a href="product.htm">{{lang.product_list}}</a>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.product_set_text1}}</span>
    </div>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li class="active" v-permission="'auth_product_management_notice_setting'">
          <a href="javascript:;">{{lang.product_set_text2}}</a>
        </li>
        <li v-permission="'auth_product_management_global_setting_custom'">
          <a :href="`global_setting_custom.htm`">{{lang.product_set_text3}}</a>
        </li>
        <li v-permission="'auth_product_management_global_setting_cycle'">
          <a :href="`global_setting_cycle.htm`">{{lang.product_set_text4}}</a>
        </li>
        <li v-permission="'auth_product_management_global_setting_os'">
          <a :href="`global_setting_os.htm`">{{lang.product_set_text5}}</a>
        </li>
        <li v-permission="'auth_product_management_global_setting_ratio'">
          <a :href="`global_setting_ratio.htm`">{{lang.product_set_text6}}</a>
        </li>
        <li>
          <a href="global_setting_demand.htm">{{lang.product_set_text126}}</a>
        </li>
        <li v-permission="'auth_product_management_global_setting_other'">
          <a :href="`global_setting_other.htm`">{{lang.product_set_text7}}</a>
        </li>
      </ul>
      <div class="common-tab-content">
        <h2 class="top-tit">{{lang.product_notice_manage}}<span class="des">{{lang.product_set_text125}}</span></h2>
        <div class="notice-box">
          <t-tabs v-model="params.type" @change="changeTab" placement="left" style="flex-shrink: 0; margin-right: 20px">
            <t-tab-panel :value="item.type" :label="item.name" v-for="item in notice_type"
              :key="item.type"></t-tab-panel>
          </t-tabs>
          <div class="right-box">
            <div class="search">
              <t-button @click="addRule">{{lang.product_set_text110}}</t-button>
              <div class="flex">
                <t-input :placeholder="lang.product_set_text112" v-model="params.name"
                  @keypress.enter.native="getNoticeList" clearable @clear="getNoticeList"> </t-input>
                <t-input :placeholder="lang.product_set_text113" v-model="params.product_name"
                  @keypress.enter.native="getNoticeList" clearable @clear="getNoticeList"> </t-input>
              </div>
            </div>
            <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading"
              table-layout="fixed" @sort-change="sortChange" :hide-sort-tips="hideSortTips">
              <template slot="sortIcon">
                <t-icon name="caret-down-small"></t-icon>
              </template>
              <template #product="{row}">
                <span v-html="row.allName"></span>
              </template>
              <template #title-slot-name>
                <span v-for="item in notice_setting" class="config-tit" :key="item.id"> {{item.name_lang}} </span>
              </template>
              <template #product-config="{row}">
                <div class="config-box">
                  <span v-for="(item,index) in notice_setting" class="config-tit" :key="index">
                    <t-switch :custom-value="[1,0]" v-model="row.notice_setting[item.name]"
                      @change="changeStatus($event,item, row.id)"> </t-switch>
                  </span>
                </div>
              </template>
              <template #op="{row}">
                <t-tooltip :content="lang.edit" :show-arrow="false" theme="light">
                  <t-icon name="edit-1" class="common-look" @click="updateRule(row)"></t-icon>
                </t-tooltip>
                <t-tooltip :content="lang.delete" :show-arrow="false" theme="light">
                  <t-icon name="delete" class="common-look" @click="deleteRule(row)" v-if="row.is_default === 0">
                  </t-icon>
                </t-tooltip>
              </template>
            </t-table>
          </div>
        </div>
      </div>
    </t-card>
    <!-- 规则组 -->
    <t-dialog :visible.sync="visible" :header="optTip" :on-close="close" :footer="false" width="1300"
      class="rule-dialog" placement="center">
      <t-form :rules="rules" :data="ruleForm" ref="ruleDialog" @submit="onSubmit" reset-type="initial"
        label-align="top">
        <t-form-item :label="lang.product_set_text116" name="name">
          <t-input :placeholder="lang.product_set_text116" v-model="ruleForm.name"
            :disabled="optType === 'edit' && ruleForm.is_default === 1" />
        </t-form-item>
        <t-form-item :label="lang.product_set_text117" name="notice_setting">
          <t-checkbox-group v-model="ruleForm.notice_setting">
            <t-checkbox v-for="item in notice_setting" :key="item.name"
              :value="item.name">{{item.name_lang}}</t-checkbox>
          </t-checkbox-group>
        </t-form-item>
        <t-form-item :label="lang.product_set_text118" name="product_id">
          <template slot="label">
            {{lang.product_set_text118}}<span class="des">({{lang.product_set_text124}})</span>
          </template>
          <div class="flex">
            <div class="left">
              <div class="top">
                <t-input v-model="leftSearch" @change="changeLeftSearch" :placeholder="lang.product_set_text119"
                  clearable> </t-input>
              </div>
              <t-tree ref="tree" v-model="ruleForm.product_id"
                :keys="{children: 'children', label: 'name', value: 'key'}" :data="productList" :checkable="true"
                value-mode="onlyLeaf" :filter="filterProduct" class="t-table__content">
                <template #label="{node}">
                  <t-tooltip :content="node.label" :disabled="node.label.length < 23">
                    {{node.label}}
                  </t-tooltip>
                </template>
              </t-tree>
            </div>
            <div class="right">
              <div class="top">
                {{lang.product_set_text120}}({{ruleForm.product_id.length}})
              </div>
              <div class="right-table">
                <div class="top-opt">
                  <div class="l-opt">
                    <template v-if="!(optType === 'edit' && ruleForm.is_default === 1)">
                      <t-button @click="btachDelProduct">{{lang.product_set_text121}}</t-button>
                      <span class="des">{{lang.product_set_text122}}</span>
                    </template>
                  </div>
                  <t-input v-model="rightSearch" clearable :placeholder="lang.product_set_text113"></t-input>
                </div>
                <t-table row-key="id" :data="calcSelectedProductList" :columns="selectProductColumns" :hover="hover"
                  bordered @sort-change="sortChange" :hide-sort-tips="hideSortTips" resizable :max-height="284"
                  :selected-row-keys="selectedRowKeys" @select-change="selectChange">
                  <template slot="sortIcon">
                    <t-icon name="caret-down-small"></t-icon>
                  </template>
                  <template #op="{row}">
                    <t-tooltip :content="lang.user_api_text9" :show-arrow="false" theme="light">
                      <t-icon name="delete" class="common-look" @click="handleDelProduct([row.id])"
                        v-if="!(optType === 'edit' && ruleForm.is_default === 1)"> </t-icon>
                    </t-tooltip>
                  </template>
                </t-table>
              </div>
            </div>
          </div>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="close">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 删除 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureDel" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/global_setting_notice.js"></script>
{include file="footer"}

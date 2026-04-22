{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/message_list.css">
<div id="content" class="log-system" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <!-- <li :class="{active:params.type == 'system'}" @click="changeType('system')">
          <a href="javascript:;">{{lang.notice_text1}}</a>
        </li> -->
        <li :class="{active:params.type== 'idcsmart'}" @click="changeType('idcsmart')">
          <a href="javascript:;">{{lang.notice_text2}}</a>
        </li>
      </ul>
      <div class="message-search">
        <div class="message-search-left">
          <t-button theme="primary" @click="handelRead([],true)">{{lang.notice_text14}}</t-button>
          <t-button @click="handelDelete(selectedRowKeys)" theme="danger">
            {{lang.notice_text12}}
          </t-button>
          <t-button theme="default" class="com-gray-btn"
            @click="handelRead(selectedRowKeys)">
            {{lang.notice_text13}}
          </t-button>
        </div>
        <div class="message-search-right">
          <t-select v-model="params.read" style="width: 150px;" @change="search">
            <t-option value="" :label="lang.notice_text11"></t-option>
            <t-option :value="0" :label="lang.notice_text10"></t-option>
            <t-option :value="1" :label="lang.notice_text9"></t-option>
          </t-select>
          <t-input v-model="params.keywords" class="search-input" :placeholder="lang.notice_text8" style="width: 230px;"
            @keypress.enter.native="search" :on-clear="clearKey" clearable>
          </t-input>
          <t-button @click="search">{{lang.query}}</t-button>
        </div>
      </div>
      <t-table row-key="id" :data="data" size="medium" :hide-sort-tips="true" :columns="columns" :hover="hover"
        :loading="loading" :selected-row-keys="selectedRowKeys" @select-change="rehandleSelectChange"
        :table-layout="tableLayout ? 'auto' : 'fixed'">
        <template #title="{row}">
          <a :href="`message_detail.htm?id=${row.id}&type=${row.type}`" class="aHover">
            {{row.title || '--'}}
          </a>
        </template>
        <template #accept_time="{row}">
          {{moment(row.accept_time * 1000).format('YYYY-MM-DD HH:mm')}}
        </template>
        <template #op="{row}">
          <t-tooltip :content="lang.notice_text4" :show-arrow="false" theme="light" v-if="row.read == 0">
            <t-icon name="check" color="var(--td-brand-color)" style="margin-right: 10px;" @click="handelRead([row.id])"
              class="common-look">
            </t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.notice_text7" :show-arrow="false" theme="light">
            <t-icon name="delete" color="var(--td-brand-color)" class="common-look" @click="handelDelete([row.id])">
            </t-icon>
          </t-tooltip>
        </template>
      </t-table>
      <com-pagination v-if="total" :total="total"
        :page="params.page" :limit="params.limit"
        @page-change="changePage">
      </com-pagination>
    </t-card>

    <!-- 删除弹窗 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
      <template slot="footer">
        <t-button theme="primary" @click="sureDel" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>



  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/message_list.js"></script>
{include file="footer"}

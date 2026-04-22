{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/message_list.css">
<div id="content" class="log-system" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="message-detail" v-loading="loading">
        <div class="top-box">
          <div class="message-back" @click="goBack">
            <t-icon class="common-look" name="chevron-left-circle"></t-icon>
            {{lang.notice_text17}}
          </div>
          <div class="right-btn">
            <t-button variant="outline" theme="primary" v-if="before.id"
              @click="goNextMessage(before.id)">{{lang.notice_text19}}</t-button>
            <t-button variant="outline" theme="primary" v-if="next.id"
              @click="goNextMessage(next.id)">{{lang.notice_text20}}</t-button>
            <t-button variant="outline" theme="danger" @click="handelDelete([id])"
              :loading="submitLoading">{{lang.notice_text7}}</t-button>
          </div>
        </div>
        <div class="message-title">{{messageDetail.title}}</div>
        <div class="message-time">
          <span class="message-time-text">{{moment(messageDetail.create_time * 1000).format('YYYY-MM-DD HH:mm')}}</span>
          <span class="message-time-type">
            {{lang.notice_text18}}：{{messageDetail.type === 'system' ? lang.notice_text1 : lang.notice_text2}}
          </span>
        </div>
        <div class="message-main" v-html="messageDetail.content"></div>
      </div>
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
<script src="/{$template_catalog}/template/{$themes}/js/message_detail.js"></script>
{include file="footer"}

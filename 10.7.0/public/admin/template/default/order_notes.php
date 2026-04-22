{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/common/viewer.min.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/client.css">
<!-- =======内容区域======= -->
<div id="content" class="order-details hasCrumb client_records" v-cloak>
  <com-config>
    <div class="com-crumb">
      <span>{{lang.business_manage}}</span>
      <t-icon name="chevron-right"></t-icon>
      <span style="cursor: pointer;" @click="goOrder">{{lang.order_manage}}</span>
      <t-icon name="chevron-right"></t-icon>
      <span class="cur">{{lang.notes}}</span>
      <span class="back-text" @click="goBack">
        <t-icon name="chevron-left-double"></t-icon>{{lang.back}}
      </span>
    </div>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li v-if="$checkPermission('auth_business_order_detail_order_detail_view') || $checkPermission('auth_user_detail_order_check_order')">
          <a :href="`order_details.htm?id=${id}`">{{lang.create_order_detail}}</a>
        </li>
        <li v-permission="'auth_business_order_detail_refund_record_view'">
          <a :href="`order_refund.htm?id=${id}`">{{lang.refund_record}}</a>
        </li>
        <li v-permission="'auth_business_order_detail_transaction'">
          <a :href="`order_flow.htm?id=${id}`">{{lang.flow}}</a>
        </li>
        <li v-if="hasCostPlugin" v-permission="'auth_addon_cost_pay_show_tab'">
          <a :href="`plugin/cost_pay/order_cost.htm?id=${id}`">{{lang.piece_cost}}</a>
        </li>
        <li class="active" v-permission="'auth_order_detail_info_record_view'">
          <a>{{lang.notes}}</a>
        </li>
      </ul>
      <t-button :disabled="totalUpdate" @click="addRecord"
        v-permission="'auth_order_detail_info_record_create_record'">{{lang.order_text53}}{{lang.upstream_text65}}</t-button>
      <t-loading :loading="recordLoading" size="small">
        <div class="record-list">
          <div class="r-item" v-for="(item,index) in recordsList" :key="index">
            <t-icon name="time" class="time-icon" size="16"></t-icon>
            <div class="top">
              <p class="left">
                <span class="time">{{moment(item.create_time * 1000).format('YYYY-MM-DD HH:mm:ss')}}</span>
                <span class="user">{{item.admin_name}}</span>
              </p>
              <div class="opt" v-if="!(item.edit && optType === 'add')">
                <t-icon name="edit" size="18" class="edit" @click="editItem(item)"
                  v-if="!totalUpdate && $checkPermission('auth_order_detail_info_record_update_record')">
                </t-icon>
                <t-icon name="delete" size="18" class="del" @click="delItem(item)"
                  v-permission="'auth_order_detail_info_record_delete_record'">
                </t-icon>
              </div>
            </div>
            <div class="con">
              <t-form :data="recordFrom" :rules="rules" ref="record" @submit="confirmRecord">
                <div class="des" v-show="!item.edit && item.content">{{item.content}}</div>
                <div v-show="item.edit">
                  <t-form-item label=" ">
                    <t-textarea v-model="recordFrom.content" :placeholder="`${lang.upstream_text65}`" name="description"
                      :maxlength="300" :autosize="{ minRows: 3, maxRows: 5 }" />
                  </t-form-item>
                </div>
                <div class="file" v-if="(!item.edit && item.attachment.length > 0) || item.edit">
                  <div class="left">
                    <div v-show="item.edit">{{lang.order_attachment}}：</div>
                    <t-upload theme="custom" multiple v-model="recordFrom.attachment" :before-upload="beforeUploadfile"
                      v-show="item.edit" draggable :action="uploadUrl" :format-response="formatResponse"
                      :headers="uploadHeaders" theme="file" @fail="handleFail" @progress="uploadProgress"
                      @success="uploadSuccess">
                      <div class="upload-tip">
                        <span>{{lang.records_upload_tip}}</span>
                      </div>
                    </t-upload>
                    <div class="f-item" v-for="(el,ind) in item.attachment" :key="ind">
                      <t-icon name="attach" size="16"></t-icon>
                      <span class="name"
                        @click="downloadfile(el,el.split('^')[1])">{{typeof el === 'string' ? el.split('^')[1] :el.response.save_name.split('^')[1] }}</span>
                      <t-icon class="delfile" name="close" size="16" color="#ccc" @click.native="delfiles(index,ind)"
                        v-if="item.edit">
                      </t-icon>
                    </div>
                  </div>
                  <div class="submit" v-show="item.edit">
                    <t-button theme="primary" type="submit" class="submit-btn" :loading="submitLoading"
                      :disabled="!recordFrom.content && recordFrom.attachment.length === 0">{{lang.sure}}</t-button>
                    <t-button theme="default" variant="base" @click="cancelItem(item)">{{lang.cancel}}</t-button>
                  </div>
                </div>
              </t-form>
            </div>
          </div>
        </div>
      </t-loading>
      <p class="loading">{{loadingText}}</p>
      <!-- 删除弹窗 -->
      <t-dialog theme="warning" :header="lang.sureDelete" :visible.sync="delVisible">
        <template slot="footer">
          <t-button theme="primary" @click="sureDelUser" :loading="submitLoading">{{lang.sure}}</t-button>
          <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
        </template>
      </t-dialog>
      <!-- 图片预览 -->
      <div>
        <img id="viewer" :src="preImg" alt="">
      </div>
    </t-card>
    <div class="deleted-svg">
      <img :src="`${rootRul}img/deleted.svg`" alt="" v-show="formData.is_recycle">
    </div>
  </com-config>
</div>
<script src="/{$template_catalog}/template/{$themes}/js/common/viewer.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/client.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/order_notes.js"></script>
{include file="footer"}

{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/supplier_list.css">
<div id="content" class="supplier_list" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="common-header top">
        <div class="flex">
          <t-button @click="addSupplier"
            v-permission="'auth_upstream_downstream_supplier_create_supplier'">{{lang.upstream_text41}}
          </t-button>
          <t-button theme="default" class="com-gray-btn" @click="handelOpenConfig">
            <div class="status-btn">
              <div class="dot-box" :class="{'active': creditData.supplier_credit_warning_notice == 1}"></div>
              <div>{{lang.upstream_text118}}</div>
            </div>
          </t-button>
        </div>
      </div>
      <t-table row-key="id" :data="data" size="medium" :columns="columns" @sort-change="sortChange" :hover="hover"
        :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width">
        <template #num="{row}">
          <span>{{row.host_num}}/{{row.product_num}}</span>
        </template>
        <template #name="{row}">
          <span style="color: var(--td-brand-color); cursor: pointer;" @click="goDetail(row.id)"
            v-if="$checkPermission('auth_upstream_downstream_supplier_detail')">{{row.name}}</span>
          <span v-else>{{row.name}}</span>
        </template>
        <template #type="{row}">
          {{typeObj[row.type]}}
        </template>
        <template #credit="{row}">
          <div class="credit-right" v-loading="row.loadingCredit">
            {{row.credit || '--'}}
            <t-tooltip :content="lang.upstream_text130" :show-arrow="false" theme="light" placement="top-right">
              <t-icon @click="handelRefreshCredit(row)" name="refresh" class="common-look"></t-icon>
            </t-tooltip>
          </div>
        </template>
        <template #currency_name="{row}">
          {{row.currency_name}}({{row.currency_code}})
        </template>
        <template #title-slot-name>
          {{lang.upstream_text77}}
          <t-tooltip :content="lang.upstream_text80" :show-arrow="false" theme="light" placement="top-right">
            <t-icon name="help-circle"></t-icon>
          </t-tooltip>
        </template>
        <template #rate="{row}">
          {{row.rate}}
          <t-tooltip :content="lang.invoice_text19" :show-arrow="false" theme="light">
            <t-icon name="edit-1" @click="editRate(row)" class="common-look" v-if="row.auto_update_rate === 0">
            </t-icon>
          </t-tooltip>
        </template>
        <template #auto_update_rate="{row}">
          <t-switch size="medium" :custom-value="[1,0]" :value="row.auto_update_rate"
            @change="changeStatus($event, row)"></t-switch>
          <t-tooltip :show-arrow="false" theme="light" placement="top-right">
            <template slot="content">
              <p class="tip">{{lang.upstream_text79}}：</p>
              <p class="tip">
                {{ row.rate_update_time>0 ? moment(row.rate_update_time * 1000).format('YYYY-MM-DD HH:mm') : '--'}}
              </p>
            </template>
            <t-icon name="help-circle"></t-icon>
          </t-tooltip>
        </template>
        <template #status="{row}">
          <span>
            <t-icon v-if="row.status" name="check-circle-filled" size="18px" style="color: var(--td-brand-color)"></t-icon>
            <t-tooltip :content="row.resgen" :show-arrow="false" theme="light" v-else>
              <t-icon name="error-circle-filled" size="18px" style="color: #E34D59;"></t-icon>
            </t-tooltip>
          </span>
        </template>
        <template #op="{row}">
          <t-tooltip :content="lang.look" :show-arrow="false" theme="light">
            <t-icon name="view-module" @click="goDetail(row.id)" class="common-look"
              v-permission="'auth_upstream_downstream_supplier_detail'"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.invoice_text19" :show-arrow="false" theme="light">
            <t-icon name="edit-1" @click="handelEdit(row)" class="common-look"
              v-permission="'auth_upstream_downstream_supplier_update_supplier'"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.box_content8" :show-arrow="false" theme="light">
            <t-icon name="delete" @click="handelDel(row.id)" class="common-look"
              v-permission="'auth_upstream_downstream_supplier_delete_supplier'"></t-icon>
          </t-tooltip>
        </template>
      </t-table>
      <com-pagination v-if="total" :total="total"
        :page="params.page" :limit="params.limit"
        @page-change="changePage">
      </com-pagination>
    </t-card>
    <!-- 配置弹窗 -->
    <t-dialog :header="lang.upstream_text42" :visible.sync="configVisble" :footer="false" width="650" @closed="diaClose"
      class="config-dialog">
      <t-form :rules="rules" ref="userDialog" :data="formData" @submit="onSubmit" :label-width="120"
        reset-type="initial">
        <t-form-item :label="item.title" :name="item.name" v-for="item in configData" :key="item.title">
          <!-- text -->
          <t-input style="width: calc(100% - 30px);" v-if="item.type==='text'"
            :disabled="(item.disableEdit && editId !='')" v-model="formData[item.name]"
            :placeholder="item.tip ? item.tip : item.title"></t-input>
          <!-- password -->
          <t-input v-if="item.type==='password'" type="password" v-model="formData[item.name]"
            :placeholder="item.tip ? item.tip :item.title"></t-input>
          <!-- textarea -->
          <t-textarea style="width: calc(100% - 30px);" v-if="item.type==='textarea'" v-model="formData[item.name]"
            :placeholder="item.tip ? item.tip :lang.input + item.title">
          </t-textarea>
          <!-- radio -->
          <t-radio-group style="width: calc(100% - 30px);" v-if="item.type==='radio'" v-model="formData[item.name]"
            :options="computedOptions(item.options)">
          </t-radio-group>
          <!-- checkbox -->
          <t-checkbox-group style="width: calc(100% - 30px);" v-if="item.type==='checkbox'"
            v-model="formData[item.name]" :options="item.options">
          </t-checkbox-group>
          <!-- select -->
          <t-select style="width: calc(100% - 30px);" v-if="item.type==='select'" v-model="formData[item.name]"
            :placeholder="item.tip ? item.tip :item.title">
            <t-option v-for="ele in computedOptions(item.options)" :value="ele.value" :label="ele.label"
              :key="ele.value">
            </t-option>
          </t-select>
          <t-tooltip :content="item.tip" :show-arrow="false" theme="light" v-if="item.tip">
            <t-icon name="help-circle" size="18px"
              style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
          </t-tooltip>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="configVisble=false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 汇率 -->
    <t-dialog :header="lang.upstream_text81" :visible.sync="rateVisible" :footer="false" width="550" @closed="diaClose"
      class="config-dialog">
      <t-form :rules="rules" ref="userDialog" :data="formData" @submit="submitRate" :label-width="120"
        reset-type="initial">
        <t-form-item :label="lang.upstream_text52">
          <t-input :value="formData.name" disabled></t-input>
        </t-form-item>
        <t-form-item :label="lang.upstream_text67">
          <t-input :value="typeObj[formData.type]" disabled></t-input>
        </t-form-item>
        <t-form-item :label="lang.upstream_text76">
          <t-input :value="formData.currency_name + '(' + formData.currency_code + ')'" disabled></t-input>
        </t-form-item>
        <t-form-item :label="lang.upstream_text79">
          <t-input
            :value="formData.rate_update_time>0 ? moment(formData.rate_update_time * 1000).format('YYYY-MM-DD HH:mm') : '--'"
            disabled></t-input>
        </t-form-item>
        <t-form-item :label="lang.upstream_text82">
          <t-switch size="medium" :custom-value="[1,0]" v-model="formData.auto_update_rate"></t-switch>
        </t-form-item>
        <t-form-item :label="lang.upstream_text77" name="rate">
          <t-input-number v-model="formData.rate" theme="normal" :min="0.00001" :decimal-places="5"
            :placeholder="lang.upstream_text80" @blur="changeRate">
          </t-input-number>
        </t-form-item>
        <t-form-item label=" ">
          <span class="s-tip">{{lang.upstream_text83}}</span>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="rateVisible=false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 余额预警 -->
    <t-dialog :header="lang.upstream_text119" :visible.sync="creditVisible" :footer="false" width="550"
      @closed="creditDiaClose" class="config-dialog">
      <t-form ref="creditDialog" :data="creditFormData" @submit="submitCredit" :label-width="120" reset-type="initial">
        <t-form-item :rules="[{ required: true, message: lang.upstream_text129, trigger: 'blur' }]"
          :label="lang.upstream_text120" name="supplier_credit_amount">
          <t-input-number v-model="creditFormData.supplier_credit_amount" theme="normal"
            :placeholder="lang.upstream_text129" :min="0" :decimal-places="2">
          </t-input-number>
        </t-form-item>
        <t-form-item :label="lang.upstream_text121" name="supplier_credit_push_frequency" :help="lang.firewall_tip67">
          <t-radio-group v-model="creditFormData.supplier_credit_push_frequency">
            <t-radio :value="1">{{lang.upstream_text122}}</t-radio>
            <t-radio :value="2">{{lang.upstream_text123}}</t-radio>
            <t-radio :value="3">{{lang.upstream_text124}}</t-radio>
          </t-radio-group>
        </t-form-item>
        <t-form-item :label="lang.upstream_text125" name="supplier_credit_warning_notice" :help="lang.firewall_tip67">
          <t-radio-group v-model="creditFormData.supplier_credit_warning_notice">
            <t-radio :value="1">{{lang.upstream_text126}}</t-radio>
            <t-radio :value="0">{{lang.upstream_text127}}</t-radio>
          </t-radio-group>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="creditVisible=false">{{lang.cancel}}</t-button>
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
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/upstream.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/supplier_list.js"></script>
{include file="footer"}

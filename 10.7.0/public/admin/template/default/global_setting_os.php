{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/global_setting.css">
<div id="content" class="product-notice-manage hasCrumb" v-cloak>
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
        <li v-permission="'auth_product_management_notice_setting'">
          <a href="global_setting_notice.htm">{{lang.product_set_text2}}</a>
        </li>
        <li v-permission="'auth_product_management_global_setting_custom'">
          <a :href="`global_setting_custom.htm`">{{lang.product_set_text3}}</a>
        </li>
        <li v-permission="'auth_product_management_global_setting_cycle'">
          <a :href="`global_setting_cycle.htm`">{{lang.product_set_text4}}</a>
        </li>
        <li class="active" v-permission="'auth_product_management_global_setting_os'">
          <a href="javascript:;">{{lang.product_set_text5}}</a>
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
        <t-tabs v-model="tab" placement="left" style="margin-top: 20px;" @change="handleTabChange">
          <t-tab-panel value="1" :label="lang.product_set_text63">
            <div style="margin-left: 20px;">
              <div class="table-top">
                <t-button @click="handelSync">{{lang.product_set_text63}}</t-button>
                <t-input v-model="params.keywords" style="width: 300px;" :placeholder="lang.product_set_text68"
                  @keypress.enter.native="searchSync">
                  <template #suffix-icon>
                    <t-icon :style="{ cursor: 'pointer' }" name="search" @click="searchSync"></t-icon>
                  </template>
                </t-input>
              </div>
              <t-table hover row-key="id" :loading="loading" :data="syncList" :columns="syncColumns">
                <template #create_time="{row}">
                  {{moment(row.create_time * 1000).format('YYYY-MM-DD HH:mm')}}
                </template>
              </t-table>
              <com-pagination v-if="params.total" :total="params.total"
                :page="params.page" :limit="params.limit"
                @page-change="changePage">
              </com-pagination>
            </div>
          </t-tab-panel>
          <t-tab-panel value="2" :label="lang.product_set_text64">
            <div style="margin-left: 20px;">
              <div class="top-opt flex">
                <t-button @click="classManage">{{lang.product_set_text84}}</t-button>
                <t-button class="com-gap" @click="createNewSys">{{lang.product_set_text85}}</t-button>
              </div>
              <t-table row-key="id" :data="systemList" size="medium" :columns="systemColumns" hover
                :loading="localLoading" table-layout="auto" display-type="fixed-width" :hide-sort-tips="true"
                drag-sort="row-handler" @drag-sort="onDragSort">
                <template #drag="{row}">
                  <t-icon name="move" style="cursor: move;"></t-icon>
                </template>
                <template #group_name="{row}">
                  <span class="class-name">
                    <img :src="`${rootRul}img/os_icon/${row.icon}.svg`" alt="" class="icon">
                    {{row.group_name}}
                  </span>
                </template>
                <template #op="{row}">
                  <div class="com-opt">
                    <t-icon name="edit-1" @click="editSystem(row)"></t-icon>
                    <t-icon name="delete" @click="delSystem(row)"></t-icon>
                  </div>
                </template>
              </t-table>
            </div>
          </t-tab-panel>

        </t-tabs>
      </div>
    </t-card>
    <!-- 同步操作系统弹窗 -->
    <t-dialog :header="lang.product_set_text63" :visible.sync="syncDialog" :footer="false" width="650"
      @closed="syncClose">
      <t-form @submit="syncSubmit">
        <div class="sync-select">
          <t-select @change="findSync" style="width: 250px;" v-model="syncType" :placeholder="lang.product_set_text69"
            clearable>
            <t-option v-for="item in moduleOption" :value="item.value" :label="item.label" :key="item.value"></t-option>
          </t-select>
        </div>
        <t-table row-key="id" :columns="proColumns" :data="nowProductList" lazy-load style="margin-bottom: 30px;"
          :selected-row-keys="selectedRowKeys" @select-change="rehandleSelectChange" max-height="400">
          <template #type="{row}">
            <div> {{ row.type === 'mf_cloud' ? lang.product_set_text70 : lang.product_set_text71 }}</div>
          </template>
        </t-table>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.product_set_text98}}</t-button>
          <t-button theme="default" variant="base" @click="syncDialog=false">{{lang.client_custom_label14}}</t-button>
        </div>
      </t-form>
    </t-dialog>

    <!-- 新增/编辑系统 -->
    <t-dialog :header="comTitle" :visible.sync="systemModel" :footer="false" width="600"
      :close-on-overlay-click="false">
      <t-form :data="createSystem" ref="cycleForm" @submit="submitSystem" :rules="cycleRules" class="system-form"
        v-if="systemModel">
        <t-form-item :label="lang.product_set_text76" name="group_id">
          <t-select v-model="createSystem.group_id" class="select"
            :placeholder="`${lang.select}${lang.product_set_text76}`" clearable>
            <t-option :key="item.id" :value="item.id" :label="item.name" v-for="item in systemGroup">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.product_set_text77" name="name" :rules="[
            { required: true, message: lang.input + lang.product_set_text77, type: 'error' },
            ]">
          <t-input v-model="createSystem.name"
            :placeholder="`${lang.input}${lang.product_set_text77}${multiliTip}`"></t-input>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}
          </t-button>
          <t-button theme="default" variant="base" @click="systemModel=false">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>
    <!-- 新增/编辑分类管理-->
    <t-dialog :header="`${lang.product_set_text84}`" :visible.sync="classModel" :footer="false" width="600"
      :close-on-overlay-click="false" class="class-dialog">
      <t-form :data="classParams" ref="classForm" @submit="submitSystemGroup" :rules="cycleRules" v-if="classModel">
        <t-form-item :label="lang.product_set_text77" name="name" :rules="[
            { required: true, message: lang.input + lang.product_set_text77, type: 'error', trigger: 'blur' }]">
          <t-input v-model="classParams.name" placeholder=" "></t-input>
        </t-form-item>
        <t-form-item :label="lang.product_set_text82" name="icon">
          <t-select v-model="classParams.icon" :popup-props="popupProps" class="custom-select"
            :placeholder="`${lang.select}${lang.product_set_text82}`" clearable>
            <img class="s-icon" :class="{noIcon: !classParams.icon}" slot="prefixIcon" style="margin-right: 8px"
              :src="`${rootRul}img/os_icon/${classParams.icon}.svg`" />
            <t-option :key="index" :value="item.value" :label="item.label" v-for="(item,index) in iconSelecet"
              class="custom-icon">
              <img :src="item.label" alt="" class="icon">
            </t-option>
          </t-select>
          <t-button theme="primary" type="submit" :loading="submitLoading" style="margin-left: 14px;">{{lang.hold}}
          </t-button>
        </t-form-item>
      </t-form>
      <!-- 分类表格 -->
      <t-table row-key="id" :data="systemGroup" size="medium" :columns="groupColumns" hover :loading="loading"
        table-layout="auto" display-type="fixed-width" :hide-sort-tips="true" max-height="450px"
        style="margin-top: 30px;" drag-sort="row-handler" @drag-sort="changeSort">
        <template slot="sortIcon">
          <t-icon name="caret-down-small"></t-icon>
        </template>
        <template #drag="{row}">
          <t-icon name="move"></t-icon>
        </template>
        <template #image_group_name="{row}">
          <span class="class-name">
            <img :src="`${rootRul}img/os_icon/${row.icon}.svg`" alt="" class="icon">
            {{row.name}}
          </span>
        </template>
        <template #op="{row}">
          <div class="com-opt">
            <t-icon name="edit-1" @click="editGroup(row)"></t-icon>
            <t-icon name="delete" @click="delGroup(row)"></t-icon>
          </div>
        </template>
      </t-table>
    </t-dialog>
    <!-- 删除镜像分组提示框 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :close-btn="false" :visible.sync="delVisible"
      class="deleteDialog">
      <template slot="footer">
        <t-button theme="primary" @click="deleteGroup" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delVisible=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
    <!-- 删除镜像系统 -->
    <t-dialog theme="warning" :header="lang.sureDelete" :close-btn="false" :visible.sync="delStymeVisble"
      class="deleteDialog">
      <template slot="footer">
        <t-button theme="primary" @click="deleteSystem" :loading="submitLoading">{{lang.sure}}</t-button>
        <t-button theme="default" @click="delStymeVisble=false">{{lang.cancel}}</t-button>
      </template>
    </t-dialog>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/global_setting_os.js"></script>
{include file="footer"}

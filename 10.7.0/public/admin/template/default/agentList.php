{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/upstream_order.css">
<div id="content" class="agent-list" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <div class="common-header">
        <div></div>
        <div class="com-search">
          <t-input v-model="params.keywords" class="search-input" :placeholder="lang.upstream_text1"
            @keypress.enter.native="search" :on-clear="clearKey" clearable>
          </t-input>
          <t-icon size="20px" name="search" @click="search" class="com-search-btn" />
        </div>
      </div>
      <t-table row-key="id" :data="data" size="medium" :columns="columns" :hover="hover" :loading="loading"
        :table-layout="tableLayout ? 'auto' : 'fixed'">
        <template #cpu="{row}">
          <span>{{row.cpu_min}} - {{row.cpu_max}}{{lang.upstream_text2}}</span>
        </template>
        <template #memory="{row}">
          <span>{{row.memory_min}} - {{row.memory_max}}GB</span>
        </template>
        <template #disk="{row}">
          <span>{{row.disk_min}} - {{row.disk_max}}GB</span>
        </template>
        <template #bandwidt="{row}">
          <span>{{row.bandwidth_min}} - {{row.bandwidth_max}}Mbps</span>
        </template>
        <template #flow="{row}">
          <span>{{row.flow_min}} - {{row.flow_max}}G</span>
        </template>
        <template #price="{row}">
          <span>{{currency_prefix}}{{row.price}}/{{row.cycle}} {{lang.upstream_text3}}</span>
        </template>
        <template #op="{row}">
          <t-tooltip :content="lang.upstream_text4" v-if="row.agent === 0" :show-arrow="false" theme="light">
            <t-icon name="swap" size="18px" @click="editGoods(row)" class="common-look"></t-icon>
          </t-tooltip>
          <t-tooltip :content="lang.upstream_text5" v-if="row.agent === 1" :show-arrow="false" theme="light">
            <t-icon name="swap" size="18px" class="greey-color"></t-icon>
          </t-tooltip>
        </template>
      </t-table>
      <com-pagination v-if="total" :total="total" :page="params.page" :limit="params.limit" @page-change="changePage">
      </com-pagination>
    </t-card>
    <!-- 代理商品 -->
    <t-dialog :header="lang.upstream_text4" width="640" :visible.sync="productModel" :footer="false"
      @close="closeProduct">
      <div class="goods-info">
        <div>
          <div class="leble">{{lang.upstream_text6}}：</div>
          <div class="value">{{curObj.supplier_name}}</div>
        </div>
        <div>
          <div class="leble">{{lang.upstream_text7}}：</div>
          <div class="value">{{curObj.name}}</div>
        </div>
        <div>
          <div class="leble">{{lang.upstream_text8}}：</div>
          <div class="value">{{currency_prefix}}{{curObj.price}}/{{curObj.cycle}} {{lang.upstream_text3}}</div>
        </div>
        <div>
          <div class="leble">{{lang.upstream_text9}}：</div>
          <div class="value">{{curObj.description}}</div>
        </div>
      </div>
      <t-form :data="productData" ref="productForm" @submit="submitProduct" :rules="productRules" reset-type="initial">
        <t-form-item :label="lang.upstream_text10" name="username" class="first-item">
          <div style="width: 100%;">
            <div style="display: flex; align-items: center;">
              <t-input v-model="productData.username" :placeholder="lang.upstream_text11"></t-input>
              <t-tooltip :content="lang.upstream_text12" :show-arrow="false" theme="light">
                <t-icon name="help-circle" size="18px"
                  style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
              </t-tooltip>
            </div>
            <p class="jump-link">
              {{curObj.login_url}}
              <a target="_blank" :href="curObj.login_url"
                style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;">
                {{lang.upstream_text13}}
              </a>
            </p>
          </div>
        </t-form-item>
        <t-form-item :label="lang.upstream_text14" name="token">
          <t-input v-model="productData.token" :placeholder="lang.upstream_text15"></t-input>
        </t-form-item>
        <t-form-item :label="lang.upstream_text16" name="secret">
          <t-textarea v-model="productData.secret" :placeholder="lang.upstream_text17">
          </t-textarea>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.upstream_text27}}</t-button>
          <t-button theme="default" variant="base" @click="closeProduct">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>

    <!-- 编辑商品 -->
    <t-dialog :header="isEdit ? lang.edit_goods : lang.add_other_product" :width="isEn ? 750 : 620"
      :visible.sync="upstreamModel" :footer="false" @close="closeUpstream" class="pro-dialog" placement="center">
      <t-form :data="upstreamData" ref="upstreamForm" @submit="submitUpstream" :rules="upstreamRules"
        reset-type="initial">
        <t-form-item :label="lang.upstream_text6" name="supplier_id" :help="supplierTip" class="supplier-select">
          <t-select v-model="upstreamData.supplier_id" :placeholder="lang.upstream_text6" filterable
            :scroll="{ type: 'virtual' }" :popup-props="{ overlayInnerStyle: { height: '200px' } }"
            @change="supplierChange" :disabled="isEdit">
            <t-option v-for="item in supplierOption" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.product" name="upstream_product_id">
          <t-select v-model="upstreamData.upstream_product_id" :placeholder="lang.product" filterable
            :scroll="{ type: 'virtual' }" :popup-props="{ overlayInnerStyle: { height: '200px' } }" :disabled="isEdit"
            @change="chooseProduct">
            <t-option v-for="item in goodsOption" :value="item.id" :label="`#${item.id}-${item.name}`" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.product_name" name="name">
          <t-input v-model="upstreamData.name" :placeholder="lang.product_name"></t-input>
        </t-form-item>
        <!-- 类型为default 并且是专业|企业版 -->
        <t-form-item :label="lang.upstream_text101" name="mode" v-if="calcType === 'default'">
          <t-radio-group v-model="upstreamData.mode" @change="changeMode" :disabled="isEdit">
            <t-radio value="only_api">{{lang.upstream_text102}}</t-radio>
            <t-radio value="sync" v-show="isShowSync" :disabled="isEdit || edition !== 1">
              {{lang.upstream_text103}}
              <t-tooltip :content="lang.upstream_text104 + '\n' + lang.upstream_text105  + '\n' + lang.upstream_text106"
                :show-arrow="false" theme="light" placement="top-left" overlay-class-name="pre-wrap">
                <t-icon name="help-circle" size="16px"></t-icon>
              </t-tooltip>
            </t-radio>
          </t-radio-group>
        </t-form-item>
        <!-- 以本地商品代理 -->
        <template v-if="upstreamData.mode === 'sync'">
          <t-form-item :label="lang.upstream_text107" class="required">
            <t-form-item name="profit_type"
              :help="upstreamData.profit_type === 0 ? lang.upstream_text114 : lang.upstream_text115">
              <t-radio-group v-model="upstreamData.profit_type" @change="changeWay('profit_percent')">
                <t-radio :value="0">{{lang.percent}}</t-radio>
                <t-radio :value="1">{{lang.upstream_text108}}</t-radio>
              </t-radio-group>
            </t-form-item>
            <t-form-item name="profit_percent" class="profit-input"
              :rules="[{required: true,message: lang.input, type: 'error'}]" v-show="upstreamData.profit_type === 0"
              help=" ">
              <t-input-number v-model="upstreamData.profit_percent" theme="normal" :decimal-places="2"
                :placeholder="lang.input" suffix="%"></t-input-number>
              <t-tooltip :content="lang.upstream_text113" :show-arrow="false" theme="light">
                <t-icon name="help-circle" size="18px"
                  style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
              </t-tooltip>
            </t-form-item>
          </t-form-item>
        </template>
        <!-- 以接口代理 -->
        <template v-else>
          <t-form-item :label="lang.upstream_text134" class="required">
            <t-radio-group v-model="upstreamData.price_basis">
              <t-radio value="standard">{{lang.upstream_text135}}
                <t-tooltip :content="lang.upstream_text137" :show-arrow="false" theme="light">
                  <t-icon name="help-circle" size="18px"
                    style="color: var(--td-brand-color); cursor: pointer; margin-left: 5px;"></t-icon>
                </t-tooltip>
              </t-radio>
              <t-radio value="agent">{{lang.upstream_text136}}
                <t-tooltip :content="lang.upstream_text138" :show-arrow="false" theme="light">
                  <t-icon name="help-circle" size="18px"
                    style="color: var(--td-brand-color); cursor: pointer; margin-left: 5px;"></t-icon>
                </t-tooltip>
              </t-radio>
            </t-radio-group>
          </t-form-item>
          <!-- 利润方式改为三种：新购，续费，升级 -->
          <t-form-item :label="lang.upstream_text84" class="required">
            <t-form-item name="profit_type">
              <t-radio-group v-model="upstreamData.profit_type" @change="changeWay('profit_percent')">
                <t-radio :value="0">{{lang.percent}}</t-radio>
                <t-radio :value="1">{{lang.fixed}}{{lang.upstream_text73}}</t-radio>
              </t-radio-group>
            </t-form-item>
            <t-form-item name="profit_percent" class="profit-input" v-if="upstreamData.profit_type === 0">
              <t-input-number v-model="upstreamData.profit_percent" theme="normal" :decimal-places="2"
                :placeholder="lang.input" suffix="%"></t-input-number>
              <t-tooltip :content="lang.upstream_text20" :show-arrow="false" theme="light">
                <t-icon name="help-circle" size="18px"
                  style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
              </t-tooltip>
            </t-form-item>
            <t-form-item name="profit_percent" v-else class="profit-input">
              <t-input-number v-model="upstreamData.profit_percent" theme="normal" :decimal-places="2"
                :placeholder="lang.input"></t-input-number>
            </t-form-item>
          </t-form-item>
          <!--  续费 -->
          <t-form-item :label="lang.upstream_text85" class="required">
            <t-form-item name="renew_profit_type">
              <t-radio-group v-model="upstreamData.renew_profit_type" @change="changeWay('renew_profit_percent')">
                <t-radio :value="0">{{lang.percent}}</t-radio>
                <t-radio :value="1">{{lang.fixed}}{{lang.upstream_text73}}</t-radio>
              </t-radio-group>
            </t-form-item>
            <t-form-item name="renew_profit_percent" v-if="upstreamData.renew_profit_type === 0" class="profit-input">
              <t-input-number v-model="upstreamData.renew_profit_percent" :placeholder="lang.input" theme="normal"
                :decimal-places="2" suffix="%"></t-input-number>
              <t-tooltip :content="lang.upstream_text20" :show-arrow="false" theme="light">
                <t-icon name="help-circle" size="18px"
                  style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
              </t-tooltip>
            </t-form-item>
            <t-form-item name="renew_profit_percent" v-else class="profit-input">
              <t-input-number v-model="upstreamData.renew_profit_percent" :placeholder="lang.input" theme="normal"
                :decimal-places="2"></t-input-number>
            </t-form-item>
          </t-form-item>
          <!-- 升级 -->
          <t-form-item :label="lang.upstream_text86" class="required">
            <t-form-item name="upgrade_profit_type">
              <t-radio-group v-model="upstreamData.upgrade_profit_type" @change="changeWay('upgrade_profit_percent')">
                <t-radio :value="0">{{lang.percent}}</t-radio>
                <t-radio :value="1">{{lang.fixed}}{{lang.upstream_text73}}</t-radio>
              </t-radio-group>
            </t-form-item>
            <t-form-item name="upgrade_profit_percent" v-if="upstreamData.upgrade_profit_type === 0"
              class="profit-input">
              <t-input-number v-model="upstreamData.upgrade_profit_percent" :placeholder="lang.input" suffix="%"
                theme="normal" :decimal-places="2"></t-input-number>
              <t-tooltip :content="lang.upstream_text20" :show-arrow="false" theme="light">
                <t-icon name="help-circle" size="18px"
                  style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
              </t-tooltip>
            </t-form-item>
            <t-form-item name="upgrade_profit_percent" v-else class="profit-input">
              <t-input-number v-model="upstreamData.upgrade_profit_percent" :placeholder="lang.input" theme="normal"
                :decimal-places="2"></t-input-number>
            </t-form-item>
          </t-form-item>
          <t-form-item label=" " class="empty-item">
            <span class="tip">{{lang.upstream_text87}}</span>
          </t-form-item>
        </template>
        <t-form-item :label="lang.upstream_text21" key="goods_description" name="dec">
          <com-tinymce ref="comTinymce" id="goods_description" :default-value="upstreamData.description"
            :pre-placeholder="lang.upstream_text22">
          </com-tinymce>
        </t-form-item>
        <t-form-item :label="lang.upstream_text23" name="auto_setup">
          <t-switch size="large" :custom-value="[1,0]" v-model="upstreamData.auto_setup"></t-switch>
        </t-form-item>
        <t-form-item :label="lang.upstream_text24" name="certification" class="no-flex">
          <t-switch size="large" :custom-value="[1,0]" v-model="upstreamData.certification"></t-switch>
          <t-tooltip :content="lang.upstream_text25" :show-arrow="false" theme="light">
            <t-icon name="help-circle" size="18px"
              style="color: var(--td-brand-color); cursor: pointer; margin-left: 10px;"></t-icon>
          </t-tooltip>
          <div class="tips-div">{{lang.upstream_text26}}</div>
        </t-form-item>
        <t-form-item :label="lang.upstream_text46" name="sync" v-if="calcType === 'finance'" class="no-flex">
          <t-tooltip :content="lang.upstream_text47" :show-arrow="false" theme="light">
            <t-switch size="large" :custom-value="[1,0]" v-model="upstreamData.sync" disabled></t-switch>
          </t-tooltip>
          <div class="tips-div">{{lang.upstream_text48}}{{lang.upstream_text75}}</div>
        </t-form-item>
        <t-form-item :label="lang.first_group" name="firstId">
          <t-select v-model="upstreamData.firstId" :placeholder="lang.group_name" @change="changeFirId">
            <t-option v-for="item in firstGroup" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <t-form-item :label="lang.second_group" name="product_group_id">
          <t-select v-model="upstreamData.product_group_id" :placeholder="lang.group_name">
            <t-option v-for="item in tempSecondGroup" :value="item.id" :label="item.name" :key="item.id">
            </t-option>
          </t-select>
        </t-form-item>
        <div class="com-f-btn">
          <t-button theme="primary" type="submit" :loading="submitLoading">{{lang.hold}}</t-button>
          <t-button theme="default" variant="base" @click="closeUpstream">{{lang.cancel}}</t-button>
        </div>
      </t-form>
    </t-dialog>

  </com-config>
</div>
<!-- =======页面独有======= -->
<script src="/tinymce/tinymce.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/components/comTinymce/comTinymce.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/common.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/upstream.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/agentList.js"></script>
{include file="footer"}

{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/finance.css">
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/voucher.css">
</head>

<body>
  <!-- mounted之前显示 -->
  <div id="mainLoading">
    <div class="ddr ddr1"></div>
    <div class="ddr ddr2"></div>
    <div class="ddr ddr3"></div>
    <div class="ddr ddr4"></div>
    <div class="ddr ddr5"></div>
  </div>
  <div class="template" id="finance">
    <el-container>
      <aside-menu @getruleslist="getRule"></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 订单列表 -->
          <div class="finance main-card">
            <div class="finance-top">
              <div class="finance-title">{{lang.finance_title}}</div>
              <div class="finance-money-main">
                <div class="finance-balance">
                  <div class="balance-left">
                    <div class="balance-left-title">
                      {{lang.finance_text135}}
                      <span v-if="Number(accountData?.customfield?.pending_amount) > 0">
                        ({{lang.finance_text140}}
                        <span
                          class="balance-title-num">{{commonData.currency_prefix + accountData?.customfield?.pending_amount}}</span>
                        )
                      </span>
                    </div>
                    <div class="balance-left-num">
                      <span class="prefix">{{commonData.currency_prefix}}</span>
                      {{ balance }}
                    </div>
                  </div>
                  <div class="balance-right">
                    <el-button type="primary" @click="showCz"
                      v-if="commonData.recharge_open == 1">{{lang.finance_btn1}}</el-button>
                    <template v-if="isOpenwithdraw">
                      <el-button plain @click="showTx" class="balance-tx-btn"
                        v-plugin="'IdcsmartWithdraw'">{{lang.finance_btn2}}</el-button>
                      <div class="tx-list" @click="goWithdrawal" v-plugin="'IdcsmartWithdraw'">
                        <el-badge :is-dot="isdot">{{lang.finance_btn9}}</el-badge>
                      </div>
                    </template>
                  </div>
                </div>
                <div class="finance-other-money" v-if="unAmount * 1 > 0 || freeze_credit * 1 > 0 || coinData.name">
                  <div class="other-money-item" v-if="unAmount * 1 > 0">
                    <div class="other-money-item-title">{{lang.finance_text2}}</div>
                    <div class="other-money-item-value">
                      <span class="prefix">{{commonData.currency_prefix}}</span>
                      {{unAmount}}
                    </div>
                  </div>
                  <div class="other-money-item" v-if="freeze_credit * 1 > 0">
                    <div class="other-money-item-title">
                      <span>{{lang.finance_text136}}</span>
                      <el-tooltip effect="dark" :content="lang.finance_text137" placement="top">
                        <svg t="1745803081479" class="icon help-icon" viewBox="0 0 1024 1024" version="1.1"
                          xmlns="http://www.w3.org/2000/svg" p-id="14138" width="16" height="16"
                          xmlns:xlink="http://www.w3.org/1999/xlink">
                          <path
                            d="M512 97.52381c228.912762 0 414.47619 185.563429 414.47619 414.47619s-185.563429 414.47619-414.47619 414.47619S97.52381 740.912762 97.52381 512 283.087238 97.52381 512 97.52381z m0 73.142857C323.486476 170.666667 170.666667 323.486476 170.666667 512s152.81981 341.333333 341.333333 341.333333 341.333333-152.81981 341.333333-341.333333S700.513524 170.666667 512 170.666667z m45.32419 487.619047v73.142857h-68.510476l-0.024381-73.142857h68.534857z m-4.047238-362.008381c44.251429 8.923429 96.889905 51.126857 96.889905 112.518096 0 61.415619-50.151619 84.650667-68.120381 96.134095-17.993143 11.50781-24.722286 24.771048-24.722286 38.863238V609.52381h-68.534857v-90.672762c0-21.504 6.89981-36.571429 26.087619-49.883429l4.315429-2.852571 38.497524-25.6c24.551619-16.530286 24.210286-49.712762 9.020952-64.365715a68.998095 68.998095 0 0 0-60.391619-15.481904c-42.715429 8.387048-47.640381 38.521905-47.932952 67.779047v16.554667H390.095238c0-56.953905 6.534095-82.773333 36.912762-115.395048 34.03581-36.449524 81.993143-42.300952 126.268952-33.328762z"
                            p-id="14139" fill="currentColor"></path>
                        </svg>
                      </el-tooltip>
                    </div>
                    <div class="other-money-item-value">
                      <span class="prefix">{{commonData.currency_prefix}}</span>
                      {{freeze_credit}}
                    </div>
                    <div class="freeze-list" @click="handelOpenFreeze">{{lang.finance_text141}}</div>
                  </div>
                  <div class="other-money-item" v-if="coinData.name" v-plugin="'Coin'">
                    <div class="other-money-item-title">{{coinData.name}}</div>
                    <div class="other-money-item-value">
                      <span class="prefix">{{commonData.currency_prefix}}</span>
                      {{coinData.leave_amount}}
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="content_box">
              <div class="content_tab">
                <el-tabs v-model="activeIndex" @tab-click="handleClick">
                  <el-tab-pane :label="lang.finance_tab1" name="1" v-if="isShowOrderController">
                    <div class="content_table">
                      <div class="content_searchbar">
                        <div class="left_tips">
                          <el-button size="mini" type="primary" @click="handelAllPay" :loading="allLoading"
                            v-if="isShowCombine">{{lang.finance_btn10}}
                          </el-button>

                          <el-button size="mini" style="margin: 0;" type="danger"
                            @click="handelAllDel">{{lang.batch_delete}}
                          </el-button>
                          <el-button size="mini" v-plugin="'ExportExcel'" style="margin: 0;" @click="handelExport"
                            :loading="exportLoading" type="primary" plain>{{lang.batch_export}}
                          </el-button>


                          <div v-for="(item,index) in tipslist1" class="tips_item" :key="index">
                            <span class="dot" :style="{'background':item.color}"></span>
                            <span>{{item.name}}</span>
                          </div>
                        </div>
                        <div class="searchbar com-search">

                          <el-select v-model="params1.type" style="width: 2.2rem;margin-left: .2rem;" clearable
                            :placeholder="lang.finance_label22" @change="inputChange1">
                            <el-option v-for="(item, index) in orderType" :key="index" :label="item.name"
                              :value="item.value"></el-option>
                            </el-option>
                          </el-select>
                          <el-select v-model="params1.status" style="width: 1.2rem;margin-left: .2rem;" clearable
                            :placeholder="lang.finance_label4" @change="inputChange1">
                            <el-option :label="lang.finance_text3" value="Unpaid"></el-option>
                            <el-option :label="lang.finance_text4" value="Paid"></el-option>
                            </el-option>
                          </el-select>

                          <el-input v-model="params1.keywords" style="width: 2.5rem;margin-left: .2rem;"
                            :placeholder="lang.cloud_tip_2" @keypress.enter.native="inputChange1" clearable
                            @clear="getorderList">
                            <i class="el-icon-search input-search" slot="suffix" @Click="inputChange1"></i>
                          </el-input>
                        </div>
                      </div>
                      <div class="tabledata">
                        <el-table v-loading="loading1" @selection-change="handleSelectionChange" :data="dataList1"
                          style="width: 100%;margin-bottom: 20px;" :row-key="getRowKey" lazy :load="load" ref="table1"
                          :tree-props="{children: 'children', hasChildren: 'hasChildren'}">
                          <el-table-column type="selection" width="80" :reserve-selection="true"
                            :selectable="(row)=>row.status !== 'Paid' && row.status !== 'Refunded'"></el-table-column>
                          <el-table-column prop="id" label="ID" width="120" align="left">
                            <template slot-scope="scope">
                              <span class="a-text" @click="goOrderDetail(scope.row.id)">
                                {{scope.row.product_names ? scope.row.id : '--'}}
                              </span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="product_names" :label="lang.finance_label1" min-width="300"
                            :show-overflow-tooltip="true">
                            <template slot-scope="scope">
                              <span class="dot" :class="scope.row.type"></span>
                              <span v-if="scope.row.product_names" class="a-text"
                                @click="goOrderDetail(scope.row.id)">{{scope.row.product_name}}</span>
                              <span v-else>{{scope.row.product_name}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="billing_cycle" :label="lang.finance_label2" width="200">
                            <template slot-scope="scope">
                              <span v-if="scope.row.status=='Unpaid'" @click="showPayDialog(scope.row)"
                                style="cursor: pointer;">
                                <span>{{ commonData.currency_prefix + scope.row.amount}}</span>
                                <span v-if="scope.row.billing_cycle">/{{scope.row.billing_cycle}}</span>
                              </span>
                              <span v-else>
                                <span>{{ commonData.currency_prefix + scope.row.amount}}</span>
                                <span v-if="scope.row.billing_cycle">/{{scope.row.billing_cycle}}</span>
                              </span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="create_time" :label="lang.finance_label3" width="200">
                            <template slot-scope="scope">
                              <span>{{scope.row.create_time | formateTime}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="status" :label="lang.finance_label4" width="150">
                            <template slot-scope="scope">
                              <!-- 未付款 -->
                              <el-tag v-if="scope.row.status && scope.row.status=='Unpaid'" type="danger"
                                @click="showPayDialog(scope.row)" style="cursor: pointer;">
                                {{lang.finance_text3}}
                              </el-tag>
                              <!-- 已付款 -->
                              <el-tag v-if="scope.row.status && scope.row.status=='Paid'" type="success">
                                {{lang.finance_text4}}
                              </el-tag>
                              <!-- 已完成 -->
                              <el-tag v-if="scope.row.status && scope.row.status=='Refunded'">
                                {{lang.finance_text17}}
                              </el-tag>
                              <!-- 待上传 -->
                              <el-tag v-if="scope.row.status && scope.row.status=='WaitUpload'" type="warning">
                                {{lang.finance_custom1}}
                              </el-tag>
                              <!-- 待审核 -->
                              <el-tag v-if="scope.row.status && scope.row.status=='WaitReview'" type="warning">
                                {{lang.finance_custom2}}
                              </el-tag>
                              <!-- 未通过 -->
                              <el-tag v-if="scope.row.status && scope.row.status=='ReviewFail'" type="danger">
                                {{lang.finance_custom3}}
                              </el-tag>
                              {{scope.row.host_status ? status[scope.row.host_status] : null}}
                              {{scope.row.host_status || scope.row.status ? null : '--'}}
                            </template>
                          </el-table-column>
                          <el-table-column prop="gateway" :label="lang.finance_label5" width="200">
                            <template slot-scope="scope">
                              <!-- 存在支付状态 父 -->
                              <div v-if="scope.row.status">
                                <!-- 已支付 -->
                                <div v-if="scope.row.gateway">
                                  <!-- 使用余额 -->
                                  <div v-if="scope.row.credit > 0">
                                    <!-- 全部使用余额 -->
                                    <div v-if="scope.row.gateway_sign === 'credit'">
                                      <span>{{lang.finance_text5}}</span>
                                    </div>
                                    <!-- 部分使用余额 -->
                                    <div v-else>
                                      <el-popover placement="top" trigger="hover" popper-class="tooltip">
                                        <i class="el-icon-s-finance"
                                          style="color: var(--color-warning);font-size: 0.35rem;"></i>
                                        <span style="color: var(--color-warning);"> {{commonData.currency_prefix
                                                                            + scope.row.credit +
                                                                            commonData.currency_suffix}}</span>
                                        <span slot="reference" class='gateway-pay'>{{lang.finance_text5}}</span>
                                      </el-popover>
                                      <span>{{scope.row.gateway ? '+'+scope.row.gateway:''}}</span>
                                    </div>
                                  </div>
                                  <!-- 未使用余额 -->
                                  <span v-else>{{scope.row.gateway}}</span>

                                </div>
                                <!-- 未支付 -->
                                <a v-else class='gateway-pay'>--</a>
                              </div>

                            </template>
                          </el-table-column>
                          <el-table-column :label="lang.finance_label6" prop="id" width="100" align="left"
                            fixed="right">
                            <template slot-scope="scope">
                              <template v-if="scope.row.status !== 'Paid' && scope.row.status !== 'Refunded'">
                                <el-popover placement="top-start" trigger="hover">
                                  <div class="operation-box">
                                    <div slot="reference" class="operation-item"
                                      @click="openDeletaDialog(scope.row,scope.$index)">{{lang.finance_btn4}}</div>
                                    <div class="operation-item" @click="showPayDialog(scope.row)"
                                      v-if="scope.row.status === 'Unpaid'">{{lang.finance_btn3}}
                                    </div>
                                    <!-- 上传凭证 -->
                                    <div class="operation-item" @click="uploadProof(scope.row.id)"
                                      v-if="scope.row.status === 'WaitUpload'">{{lang.finance_custom4}}</div>
                                    <div class="operation-item" @click="uploadProof(scope.row.id)"
                                      v-if="scope.row.status === 'WaitReview' || scope.row.status === 'ReviewFail'">
                                      {{lang.finance_custom5}}
                                    </div>
                                  </div>
                                  <i slot="reference" class="el-icon-more"></i>
                                </el-popover>
                              </template>
                              <template v-if="scope.row.status === 'Paid' && scope.row.voucher.length > 0">
                                <el-popover placement="top-start" trigger="hover">
                                  <div class="operation-box">
                                    <div class="operation-item" @click="uploadProof(scope.row.id)">
                                      {{lang.finance_custom19}}
                                    </div>
                                  </div>
                                  <i slot="reference" class="el-icon-more"></i>
                                </el-popover>
                              </template>
                            </template>
                          </el-table-column>
                        </el-table>
                        <pagination :page-data="params1" @sizechange="sizeChange1" @currentchange="currentChange1">
                        </pagination>
                      </div>


                      <!-- 移动端显示表格开始 -->
                      <div class="mobel">
                        <div class="mob-searchbar mob-com-search">
                          <el-input class="mob-search-input" v-model="params1.keywords" :placeholder="lang.cloud_tip_2"
                            @keypress.enter.native="inputChange1" clearable @clear="getorderList">
                            <i class="el-icon-search input-search" slot="suffix" @Click="inputChange1"></i>
                          </el-input>
                        </div>
                        <div class="mob-tabledata">
                          <div class="mob-tabledata-item" v-for="item in dataList1" :key="item.id"
                            @click="showItem(item)">
                            <div class="mob-item-row mob-item-row1" @click="goOrderDetail(scope.row.id)">
                              <span>{{item.id}}</span>
                              <span>
                                <el-tag v-if="item.status"
                                  :class="item.status=='Unpaid'?'Unpaid':item.status=='Paid'?'Paid':''">
                                  {{item.status=='Unpaid'?lang.finance_text3:item.status=='Paid'?lang.finance_text4:''}}
                                </el-tag>
                              </span>
                            </div>
                            <div class="mob-item-row mob-item-row2" @click="goOrderDetail(scope.row.id)">
                              <span class="mob-item-row2-name" :title="item.product_name">
                                <span class="dot" :class="item.type"></span>
                                <span class="row2-name-text">{{item.product_name}}</span>
                              </span>
                              <span>
                                <span>{{ commonData.currency_prefix + item.amount}}</span>
                                <span v-if="item.billing_cycle">/{{item.billing_cycle}}</span>
                              </span>
                            </div>

                            <div class="mob-item-row mob-item-row-child">

                              <div class="child-row" v-for="child in item.data" :key="child.id">

                                <span class="child-row-name">{{ child.product_name?child.product_name:'--'}}</span>
                                <span>
                                  {{child.amount? commonData.currency_prefix + child.amount + commonData.currency_suffix :
                                                                    null}}
                                  {{ child.billing_cycle&&child.amount? '/' + child.billing_cycle
                                                                    : null}}
                                </span>
                                <span>{{child.host_status?status[child.host_status]:null}}
                                  {{child.host_status||child.status ? null : '--'}}</span>
                              </div>
                            </div>

                            <div class="mob-item-row mob-item-row3">
                              <span>{{item.create_time | formateTime}}</span>
                              <div v-if="item.status">
                                <!-- 已支付 -->
                                <div v-if="item.status === 'Paid'">
                                  <!-- 使用余额 -->
                                  <div v-if="item.credit > 0">
                                    <!-- 全部使用余额 -->
                                    <div v-if="item.credit == item.amount">
                                      <span>{{lang.finance_text5}}</span>
                                    </div>
                                    <!-- 部分使用余额 -->
                                    <div v-else>
                                      <el-popover placement="top" trigger="hover" popper-class="tooltip">
                                        <i class="el-icon-s-finance"
                                          style="color: var(--color-warning);font-size: 0.35rem;"></i>
                                        <span style="color: var(--color-warning);"> {{commonData.currency_prefix
                                                                            + item.credit +
                                                                            commonData.currency_suffix}}</span>
                                        <span slot="reference" class='gateway-pay'>{{lang.finance_text5}}</span>
                                      </el-popover>
                                      <span> + {{item.gateway}}</span>
                                    </div>
                                  </div>
                                  <!-- 未使用余额 -->
                                  <span v-else>{{item.gateway}}</span>
                                </div>
                                <!-- 未支付 -->
                                <a v-else class='gateway-pay' @click="showPayDialog(item)">{{lang.finance_btn3}}</a>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="bottom-text">

                          <span v-show="isEnd">{{lang.finance_text6}}</span>
                          <span v-loading=isShowMore></span>
                        </div>
                        <img v-show="isShowBackTop" class="back-top-img" @click="goBackTop"
                          src="/{$template_catalog}/template/{$themes}/img/common/toTop.png">
                      </div>

                    </div>
                  </el-tab-pane>
                  <el-tab-pane :label="lang.finance_tab2" name="2" v-if="isShowTransactionController">
                    <div class="content_table">
                      <div class="content_searchbar">
                        <div class="left_tips">
                          <!--
                          <div v-for="(item,index) in tipslist1" class="tips_item" :key="index">
                            <span class="dot" :style="{'background':item.color}"></span>
                            <span>{{item.name}}</span>
                          </div> -->
                        </div>
                        <div class="searchbar com-search">
                          <el-input v-model="params2.keywords" style="width: 3.2rem;margin-left: .2rem;"
                            :placeholder="lang.cloud_tip_2" @keypress.enter.native="inputChange2" clearable
                            @clear="getTransactionList">
                            <i class="el-icon-search input-search" slot="suffix" @Click="inputChange2"></i>
                          </el-input>
                        </div>
                      </div>
                      <div class="tabledata">
                        <el-table v-loading="loading2" :data="dataList2" style="width: 100%;margin-bottom: .2rem;">
                          <el-table-column prop="id" label="ID" width="100" align="left">
                          </el-table-column>
                          <el-table-column prop="order_id" width="130" :label="lang.finance_label7" align="left">
                            <template slot-scope="scope">
                              <div class="order_id">
                                <a v-if="scope.row.order_id !== '--'" class="a-text"
                                  @click="goOrderDetail(scope.row.order_id)">{{scope.row.order_id}}</a>
                                <span v-else>{{scope.row.order_id}}</span>
                              </div>
                            </template>
                          </el-table-column>
                          <el-table-column prop="type" width="150" :label="lang.finance_label22" align="left">
                            <template slot-scope="scope">
                              <span>{{orderTypeObj[scope.row.type] || '--'}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="amount" min-width="150" :label="lang.finance_label8" align="left">
                            <template slot-scope="scope">
                              <span>{{ commonData.currency_prefix + scope.row.amount }}
                              </span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="create_time" width="200" :label="lang.finance_label3" align="left">
                            <template slot-scope="scope">
                              <span>{{scope.row.create_time | formateTime}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="gateway" width="300" :label="lang.finance_label5" align="left">
                          </el-table-column>
                          <el-table-column prop="transaction_number" width="300" :label="lang.finance_label9"
                            align="center" :show-overflow-tooltip="true">
                            <template slot-scope="scope">
                              <span>{{scope.row.transaction_number || '--'}}</span>
                            </template>
                          </el-table-column>
                        </el-table>
                        <pagination :page-data="params2" @sizechange="sizeChange2" @currentchange="currentChange2">
                        </pagination>
                      </div>

                      <!-- 移动端显示表格开始 -->
                      <div class="mobel">
                        <div class="mob-searchbar mob-com-search">
                          <el-input class="mob-search-input" v-model="params2.keywords" :placeholder="lang.cloud_tip_2"
                            @keypress.enter.native="inputChange2" clearable @clear="getTransactionList">
                            <i class="el-icon-search input-search" slot="suffix" @Click="inputChange2"></i>
                          </el-input>
                        </div>
                        <div class="mob-tabledata">
                          <div class="mob-tabledata-item" v-for="item in dataList2" :key="item.id">
                            <div class="mob-item-row mob-item-row1">
                              <span>{{item.id}}</span>
                              <span>
                                {{item.transaction_number}}
                              </span>
                            </div>
                            <div class="mob-item-row mob-item-row2">
                              <span class="mob-item-row2-name" :title="item.product_name">
                                <span class="dot" :class="item.type"></span>
                                <span class="row2-name-text">
                                  <a v-if="item.order_id !== '--'" class="a-text"
                                    @click="goOrderDetail(item.order_id)">{{item.order_id}}</a>
                                  <span v-else class="a-text"
                                    @click="goOrderDetail(item.order_id)">{{item.order_id}}</span>
                                </span>
                              </span>
                              <span>
                                <span>{{ commonData.currency_prefix + item.amount}}</span>
                                <!-- <span v-if="item.billing_cycle">/{{item.billing_cycle}}</span> -->
                              </span>
                            </div>
                            <div class="mob-item-row mob-item-row3">
                              <span>{{item.create_time | formateTime}}</span>
                              <div>
                                {{item.gateway}}
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="bottom-text">
                          <span v-show="isEnd">{{lang.finance_text6}}</span>
                          <span v-loading=isShowMore></span>
                        </div>
                        <img v-show="isShowBackTop" class="back-top-img" @click="goBackTop"
                          src="/{$template_catalog}/template/{$themes}/img/common/toTop.png">
                      </div>


                    </div>
                  </el-tab-pane>
                  <el-tab-pane :label="lang.finance_tab3" name="3" v-if="isShowBalance">
                    <div class="content_table">
                      <div class="content_searchbar balance-searchbar">
                        <div class="left_tips">
                        </div>
                        <div class="searchbar com-search">
                        </div>
                        <div class="box" style="display:flex;">
                          <el-date-picker @change="inputChange3" v-model="date" type="daterange"
                            :range-separator=lang.finance_text18 style="width:3.5rem;margin-right:0.14rem"
                            :start-placeholder="lang.finance_text19" value-format="timestamp" align="center"
                            :end-placeholder="lang.finance_text20">
                          </el-date-picker>
                          <el-select v-model="params3.type" :placeholder="lang.finance_text21" style="width:2rem"
                            @change="inputChange3">
                            <el-option v-for="item in Object.keys(balanceType)" :key="item"
                              :label="balanceType[item].text" :value="item">{{balanceType[item].text}}
                            </el-option>
                          </el-select>
                          <el-input v-model="params3.keywords" style="width: 3.2rem;margin-left: .2rem;"
                            :placeholder="lang.cloud_tip_2" @keypress.enter.native="inputChange3" clearable
                            @clear="getCreditList">
                            <i class="el-icon-search input-search" slot="suffix" @Click="inputChange3"></i>
                          </el-input>
                        </div>
                      </div>
                      <div class="tabledata">
                        <el-table v-loading="loading3" :data="dataList3" style="width: 100%;margin-bottom: .2rem;">
                          <el-table-column prop="id" label="ID" width="100" align="left">
                          </el-table-column>
                          <el-table-column prop="amount" width="150" :label="lang.finance_label8" align="left">
                            <template slot-scope="scope">
                              <span>{{ commonData.currency_prefix + scope.row.amount}}
                              </span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="notes" :label="lang.finance_label10" align="left"
                            :show-overflow-tooltip="true">
                          </el-table-column>
                          <el-table-column prop="type" :label="lang.finance_label11" width="150" align="left">
                            <template slot-scope="scope">
                              <span class="balance-tag"
                                :class="scope.row.type">{{balanceType[scope.row.type]?.text || '--'}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column prop="create_time" width="200" :label="lang.finance_label3" align="left">
                            <template slot-scope="scope">
                              <span>{{scope.row.create_time | formateTime}}</span>
                            </template>
                          </el-table-column>
                        </el-table>
                        <pagination :page-data="params3" @sizechange="sizeChange3" @currentchange="currentChange3">
                        </pagination>
                      </div>

                      <!-- 移动端显示表格开始 -->
                      <div class="mobel">
                        <div class="mob-searchbar mob-com-search">
                          <el-input class="mob-search-input" v-model="params3.keywords" :placeholder="lang.cloud_tip_2"
                            @keypress.enter.native="inputChange3" clearable @clear="getCreditList">
                            <i class="el-icon-search input-search" slot="suffix" @Click="inputChange3"></i>
                          </el-input>
                        </div>
                        <div class="mob-tabledata">
                          <div class="mob-tabledata-item" v-for="item in dataList3" :key="item.id">
                            <div class="mob-item-row mob-item-row1">
                              <span>{{item.id}}</span>
                              <span>
                                <span class="balance-tag"
                                  :class="item.type">{{balanceType[item.type]?.text || '--'}}</span>
                              </span>
                            </div>
                            <div class="mob-item-row mob-item-row2">
                              <span class="mob-item-row2-name">
                                <span>{{ commonData.currency_prefix + item.amount}}</span>
                              </span>
                              <span>
                              </span>
                            </div>
                            <div class="mob-item-row mob-item-row-notes">
                              <span>{{item.notes}}</span>
                            </div>
                            <div class="mob-item-row mob-item-row3">
                              <span>{{item.create_time | formateTime}}</span>
                              <div>
                                {{item.gateway}}
                              </div>
                            </div>

                          </div>
                        </div>
                        <div class="bottom-text">
                          <span v-show="isEnd">{{lang.finance_text6}}</span>
                          <span v-loading=isShowMore></span>
                        </div>
                        <img v-show="isShowBackTop" class="back-top-img" @click="goBackTop"
                          src="/{$template_catalog}/template/{$themes}/img/common/toTop.png">
                      </div>
                    </div>
                  </el-tab-pane>
                  <el-tab-pane :label="coinData.name" name="7" v-if="isShowCoin && coinData.name">
                    <div class="voucher">
                      <div class="voucher-box">
                        <div class="voucher-search">
                          <el-radio-group v-model="coinType" size="medium" @input="changeCoinType">
                            <el-radio-button label="active">{{lang.coin_text7}}</el-radio-button>
                            <el-radio-button label="wait">
                              <el-badge :is-dot="waitCoinTotal > 0" :hidden="waitCoinTotal === 0"
                                style="display: block;">
                                {{lang.coin_text6}}
                              </el-badge>
                            </el-radio-button>
                            <el-radio-button label="used_up">{{lang.coin_text8}}</el-radio-button>
                            <el-radio-button label="expired">{{lang.coin_text9}}</el-radio-button>
                          </el-radio-group>
                        </div>
                        <div class="voucher-content" v-loading="coinLoading">
                          <ul>
                            <li class="item" v-for="item in coinList" :key="item.id">
                              <div class="basic">
                                <div class="l-item">
                                  <div class="price"
                                    :class="{used: coinType === 'used_up',overdue: coinType === 'expired' }">
                                    <span>{{commonData.currency_prefix}}<span
                                        class="num">{{coinType === 'wait' ? item.amount : item.leave_amount}}</span></span>
                                  </div>
                                </div>
                                <div class="r-item">
                                  <p class="tit">{{item.name}}</p>
                                  <p class="time" v-if="coinType === 'wait'">
                                    <template v-if="item.begin_time">
                                      {{item.begin_time | formateTime1}}- {{item.end_time | formateTime1}}
                                    </template>
                                    <template v-else>
                                      {{lang.coin_text17}}
                                    </template>
                                  </p>
                                  <p class="time" v-else>
                                    <template v-if="item.effective_start_time">
                                      {{item.effective_start_time | formateTime1}}-
                                      {{item.effective_end_time | formateTime1}}
                                    </template>
                                    <template v-else>
                                      {{lang.coin_text17}}
                                    </template>
                                  </p>
                                  <div class="bot">
                                    <p class="more" :class="{active: item.isShow}" @click="toggleCoin(item)">
                                      {{lang.voucher_rule}}
                                      <img src="/{$template_catalog}/template/{$themes}/img/voucher/check.png" alt="">
                                    </p>
                                    <el-button type="primary" size="mini" :loading="item.get_loading"
                                      v-if="coinType === 'wait'" @click="getCoin(item)">
                                      {{lang.voucher_get_now}}
                                    </el-button>

                                    <el-popover placement="top" trigger="click" @show="showDetail(item)"
                                      v-if="coinType === 'active' || coinType === 'used_up' || coinType === 'expired'">
                                      <el-table :data="item.use_detail_list" v-loading="item.is_loading"
                                        max-height="400">
                                        <el-table-column width="80" property="id" label="ID"></el-table-column>
                                        <el-table-column width="100" property="order_id" :label="lang.coin_text54">
                                        </el-table-column>
                                        <el-table-column width="200" property="create_time" :label="lang.coin_text55">
                                          <template slot-scope="{row}">
                                            {{row.create_time | formateTime1}}
                                          </template>
                                        </el-table-column>
                                        <el-table-column width="120" property="amount" :label="lang.coin_text56">
                                          <template slot-scope="{row}">
                                            {{commonData.currency_prefix}}{{row.amount}}
                                          </template>
                                        </el-table-column>
                                        <el-table-column width="120" property="leave_amount" :label="lang.coin_text57">
                                          <template slot-scope="{row}">
                                            {{commonData.currency_prefix}}{{row.leave_amount}}
                                          </template>
                                        </el-table-column>
                                      </el-table>
                                      <span class="detail-btn" slot="reference">{{lang.coin_text53}}</span>
                                    </el-popover>
                                  </div>
                                </div>
                                <div class="bg"
                                  :class="{used: coinType === 'used_up',overdue: coinType === 'expired' }"></div>
                              </div>
                              <div class="detail" :class="{active: item.isShow}">
                                <p v-if="item.certification_can_use === 1">{{lang.coin_text48}}</p>
                                <p v-if="item.with_event_promotion_use === 0">{{lang.coin_text49}}</p>
                                <p v-if="item.with_promo_code_use === 0">{{lang.coin_text50}}</p>
                                <p v-if="item.with_client_level_use === 0">{{lang.coin_text51}}</p>
                                <p v-if="item.with_voucher_use === 0">{{lang.coin_text52}}</p>
                                <p v-if="item.host_ids?.length > 0">
                                  {{lang.coin_text64}}：
                                  <span v-for="(el,index) in item.host_ids" :key="el">
                                    <a :href="`/productdetail.htm?id=${el}`" target="_blank"
                                      style="color: var(--color-primary);text-decoration: underline;">ID:{{el}}</a>
                                    <span v-if="index !== item.host_ids.length - 1">、</span>
                                  </span>
                                </p>
                                <template v-if="!item.host_ids || item.host_ids?.length === 0">
                                  <p v-if="item.product?.length > 0">
                                    {{lang.coin_text47}}：
                                    <span v-for="(el,index) in item.product" :key="el.id">
                                      <a :href="`/cart/goods.htm?id=${el.id}`" target="_blank"
                                        style="color: var(--color-primary);text-decoration: underline;">{{el.name}}</a>
                                      <span v-if="index !== item.product.length - 1">、</span>
                                    </span>
                                  </p>

                                  <p v-else>
                                    {{lang.coin_text74}}
                                  </p>
                                </template>
                                <p v-if="item.product_only_defence == 1">{{lang.coin_text65}}</p>
                                <p
                                  v-if="item.order_available == 1 || item.upgrade_available == 1 || item.renew_available == 1 || item.demand_available == 1">
                                  {{lang.coin_text132}}：
                                  <template v-if="item.order_available == 1">
                                    {{lang.coin_text133}}<span
                                      v-if="item.upgrade_available == 1 || item.renew_available == 1 || item.demand_available == 1">、</span>
                                  </template>
                                  <template v-if="item.renew_available == 1">
                                    {{lang.coin_text134}}<span
                                      v-if="item.upgrade_available == 1 || item.demand_available == 1">、</span>
                                  </template>
                                  <template v-if="item.upgrade_available == 1">
                                    {{lang.coin_text135}}<span v-if="item.demand_available == 1">、</span>
                                  </template>
                                  <template v-if="item.demand_available == 1">
                                    {{lang.coin_text136}}
                                  </template>
                                </p>
                                <p v-if="item.cycle_limit === 1 ">
                                  {{lang.coin_text5}}：<span v-for="(cycle_item,index) in item.cycle"
                                    :key="cycle_item">{{lang[cycle_item]}} <span
                                      v-if="index !== item.cycle.length - 1">、</span></span>
                                </p>
                              </div>
                            </li>
                          </ul>
                          <el-empty v-if="coinList.length === 0 && !coinLoading" :description=" lang.coin_text60">
                          </el-empty>
                        </div>
                      </div>
                      <pagination :page-data="coinParams" v-if="coinParams.total" @sizechange="sizeChangeCoin"
                        @currentchange="currentChangeCoin" class="voucher-page"
                        :style="{marginTop: isShowCoinDetail ? '2rem' : '.2rem'}">
                      </pagination>
                    </div>
                  </el-tab-pane>
                  <el-tab-pane :label="lang.finance_text22" name="4" v-if="isShowCash">
                    <div class="voucher">
                      <!-- 代金券 -->
                      <div class="voucher-box">
                        <el-button class="get-voucher" type="primary" @click="showVoucherDialog">{{lang.voucher_get}}
                        </el-button>
                        <div class="voucher-content" v-loading="voucherLoading">
                          <ul>
                            <li class="item" v-for="item in voucherList" :key="item.id">
                              <div class="basic">
                                <div class="l-item">
                                  <div class="price"
                                    :class="{used: item.status === 'used',overdue: item.status === 'expired' }">
                                    <span>{{commonData.currency_prefix}}<span class="num">{{item.price}}</span></span>
                                    <p class="des">
                                      {{lang.voucher_min}}：{{commonData.currency_prefix}}{{item.min_price}}
                                    </p>
                                  </div>
                                </div>
                                <div class="r-item">
                                  <p class="tit">{{item.code}}</p>
                                  <p class="time">{{item.start_time | formateTime1}}- {{item.end_time | formateTime1}}
                                  </p>
                                  <div class="bot">
                                    <p class="more" :class="{active: item.isShow}" @click="toggleVoucher(item)">
                                      {{lang.voucher_rule}}
                                      <img src="/{$template_catalog}/template/{$themes}/img/voucher/check.png" alt="">
                                    </p>
                                  </div>
                                </div>
                                <div class="bg"
                                  :class="{used: item.status === 'used', overdue: item.status === 'expired'}"></div>
                              </div>
                              <div class="detail" :class="{active: item.isShow}">
                                <p v-if="item.product.length > 0">
                                  {{lang.voucher_order_product}}：
                                  <span v-for="(el,index) in item.product" :key="el.id">{{el.name}}；
                                </p>
                                <p v-if="item.product_need.length > 0">
                                  {{lang.voucher_accout_product}}：
                                  <span v-for="(el,index) in item.product_need" :key="el.id">{{el.name}}；
                                </p>
                                <p v-if="item.user_type === 'no_host'">
                                  {{lang.voucher_no_product}}
                                </p>
                                <p v-if="item.user_type === 'need_active'">
                                  {{lang.voucher_active}}
                                </p>
                                <p v-if="item.onetime">{{lang.voucher_onetime}}</p>
                                <p v-if="item.upgrade_use">{{lang.voucher_upgrade}}</p>
                                <p v-if="item.renew_use">{{lang.voucher_renew}}</p>
                                <p v-if="!item.upgrade_use">{{lang.voucher_upgrade_no}}</p>
                                <p v-if="!item.renew_use">{{lang.voucher_renew_no}}</p>
                              </div>
                            </li>
                          </ul>


                        </div>
                        <pagination :page-data="vParams" v-if="vParams.total" @sizechange="sizeChange"
                          @currentchange="currentChange" class="voucher-page"
                          :style="{marginTop: isShowVoucherDetail ? '2rem' : '.2rem'}">
                        </pagination>
                      </div>

                      <!-- 领劵弹窗 -->
                      <el-dialog :title="lang.voucher_get" :visible.sync="dialogVisible" class="voucher-dialog">
                        <div class="voucher-content" v-loading="diaLoading">
                          <div class="empty" v-if="voucherAvailableList.length === 0">
                            <el-empty :description="lang.voucher_empty"></el-empty>
                          </div>
                          <ul v-else>
                            <li class="item" v-for="item in voucherAvailableList" :key="item.id">
                              <div class="basic">
                                <div class="l-item">
                                  <div class="price">
                                    <span>{{commonData.currency_prefix}}<span class="num">{{item.price}}</span></span>
                                    <p class="des">
                                      {{lang.voucher_min}}：{{commonData.currency_prefix}}{{item.min_price}}
                                    </p>
                                  </div>
                                </div>
                                <div class="r-item">
                                  <p class="tit">{{item.code}}</p>
                                  <p class="time">{{item.start_time | formateTime1}}- {{item.end_time | formateTime1}}
                                  <div class="bot">
                                    <p class="more" :class="{active: item.isShow}" @click="toggleVoucher(item)">
                                      {{lang.voucher_rule}}
                                      <img src="/{$template_catalog}/template/{$themes}/img/voucher/check.png" alt="">
                                    </p>
                                    <p class="receive" @click="sureGet(item)" :class="{is_get: item.is_get}">
                                      {{item.is_get ?lang.voucher_has_get: lang.voucher_get_now}}
                                    </p>
                                  </div>
                                </div>

                              </div>
                              <div class="detail" :class="{active: item.isShow}">
                                <p v-if="item.product.length > 0">
                                  {{lang.voucher_order_product}}：
                                  <span v-for="(el,index) in item.product" :key="el.id">{{el.name}}；
                                </p>
                                <p v-if="item.product_need.length > 0">
                                  {{lang.voucher_accout_product}}：
                                  <span v-for="(el,index) in item.product_need" :key="el.id">{{el.name}}；
                                </p>
                                <p v-if="item.user_type === 'no_host'">
                                  {{lang.voucher_no_product}}
                                </p>
                                <p v-if="item.user_type === 'need_active'">
                                  {{lang.voucher_active}}
                                </p>
                                <p v-if="item.onetime">{{lang.voucher_onetime}}</p>
                                <p v-if="item.upgrade_use">{{lang.voucher_upgrade}}</p>
                                <p v-if="item.renew_use">{{lang.voucher_renew}}</p>
                                <p v-if="!item.upgrade_use">{{lang.voucher_upgrade_no}}</p>
                                <p v-if="!item.renew_use">{{lang.voucher_renew_no}}</p>

                              </div>
                            </li>
                          </ul>
                        </div>
                      </el-dialog>

                    </div>
                  </el-tab-pane>
                  <el-tab-pane :label="lang.finance_text23" name="5" v-if="isShowContract">
                    <div class="content_table">
                      <div class="content_searchbar balance-searchbar">
                        <div class="left_tips">
                          <el-button @click="handeInfo" type="primary">{{lang.finance_text24}}</el-button>
                          <el-button @click="handelApplyOrder" type="primary">{{lang.finance_text25}}</el-button>
                        </div>
                        <div class="box" style="display:flex; overflow-y: auto;">
                          <el-input v-model="params4.keywords" style="width: 3.2rem;margin-left: .2rem;"
                            :placeholder="lang.finance_text26" @keypress.enter.native="inputChange4" clearable
                            @clear="getContractList">
                          </el-input>
                          <el-button style="margin-left: 0.1rem;" type="primary" @Click="inputChange4">
                            {{lang.finance_text27}}</el-button>
                        </div>
                      </div>
                      <div class="tabledata">
                        <el-table v-loading="loading5" :data="dataList5" style="width: 100%;margin-bottom: .2rem;">
                          <el-table-column prop="id" :label="lang.finance_text28" width="200" align="left">
                          </el-table-column>
                          <el-table-column :label="lang.finance_text29" align="left" :show-overflow-tooltip="true">
                            <template slot-scope="scope">
                              <span v-if="scope.row.base_contract === 1">{{lang.finance_text30}}</span>
                              <span v-else>{{handelHostName(scope.row.host)}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column :label="lang.finance_text31" width="200" align="left">
                            <template slot-scope="scope">
                              <div style="display: flex; align-items: center;">
                                <span :class="scope.row.status === 'no_sign'  ? 'has-border': '' "
                                  class="contract-status"
                                  :style="{'color':contractStatusObj[scope.row.status].color,'background':contractStatusObj[scope.row.status].background}">{{contractStatusObj[scope.row.status].label}}</span>
                                <el-popover placement="top-start" trigger="hover">
                                  <div slot="reference" style="display: flex; align-items: center;">
                                    <svg v-if="scope.row.status === 'reject' || scope.row.status === 'cancel'"
                                      t="1681982176042" class="help-icon" viewBox="0 0 1024 1024" version="1.1"
                                      xmlns="http://www.w3.org/2000/svg" p-id="8315" width="18" height="18">
                                      <path
                                        d="M511.333 63.333c-247.424 0-448 200.576-448 448s200.576 448 448 448 448-200.576 448-448-200.576-448-448-448z m271.529 719.529c-35.286 35.287-76.359 62.983-122.078 82.321-47.3 20.006-97.583 30.15-149.451 30.15-51.868 0-102.15-10.144-149.451-30.15-45.719-19.337-86.792-47.034-122.078-82.321-35.287-35.286-62.983-76.359-82.321-122.078-20.006-47.3-30.15-97.583-30.15-149.451s10.144-102.15 30.15-149.451c19.337-45.719 47.034-86.792 82.321-122.078 35.286-35.287 76.359-62.983 122.078-82.321 47.3-20.006 97.583-30.15 149.451-30.15 51.868 0 102.15 10.144 149.451 30.15 45.719 19.337 86.792 47.034 122.078 82.321 35.287 35.286 62.983 76.359 82.321 122.078 20.006 47.3 30.15 97.583 30.15 149.451s-10.144 102.15-30.15 149.451c-19.337 45.719-47.034 86.792-82.321 122.078z"
                                        fill="var(--color-primary)" p-id="8316"></path>
                                      <path
                                        d="M642.045 285.629c-26.482-39.772-72.632-61.676-129.945-61.676-45.43 0-81.73 14.938-107.891 44.4-21.679 24.415-34.958 57.378-39.469 97.974-3.153 28.378-0.747 50.163-0.462 52.553 2.091 17.549 18.02 30.084 35.56 27.991 17.549-2.09 30.081-18.011 27.991-35.56-0.019-0.161-1.845-16.636 0.52-37.916 2.077-18.688 7.877-44.708 23.717-62.547 13.679-15.406 33.317-22.895 60.034-22.895 35.722 0 62.235 11.462 76.675 33.147 15.268 22.93 14.215 52.064 6.398 70.765-4.475 10.704-25.708 30.276-42.77 46.002-35.924 33.111-73.07 67.349-73.07 108.723v61.457c0 17.673 14.327 32 32 32s32-14.327 32-32V546.59c0-0.684 0.354-7.103 12.607-21.925 10.498-12.696 25.413-26.444 39.837-39.739 24.981-23.025 48.576-44.774 58.443-68.379 8.323-19.912 11.834-42.319 10.153-64.799-1.794-24.014-9.516-46.877-22.328-66.119z"
                                        fill="var(--color-primary)" p-id="8317"></path>
                                      <path d="M512.099 702.965m-40 0a40 40 0 1 0 80 0 40 40 0 1 0-80 0Z"
                                        fill="var(--color-primary)" p-id="8318">
                                      </path>
                                    </svg>
                                  </div>
                                  <span v-if="scope.row.status === 'reject'">{{scope.row.reason}}</span>
                                </el-popover>
                                <img v-if="scope.row.status === 'complete' && scope.row.post_number" class="help-icon"
                                  @click="handelRec(scope.row)" style="width: 0.18rem;height: 0.18rem;"
                                  src="/{$template_catalog}/template/{$themes}/img/finance/icon_1.png"
                                  :alt="lang.finance_text32">
                              </div>
                            </template>
                          </el-table-column>
                          <el-table-column :label="lang.finance_label6" width="100" align="left" fixed="right">
                            <template slot-scope="scope">
                              <el-popover placement="top-start" trigger="hover">
                                <i slot="reference" class="el-icon-more"></i>
                                <div class="operation-box">
                                  <div class="operation-item" @click="handelSign(scope.row.order_id)"
                                    v-if="scope.row.status === 'no_sign'">{{lang.finance_text33}}</div>
                                  <div class="operation-item" @click="handelDetail(scope.row.id)"
                                    v-if="scope.row.status === 'review'">{{lang.finance_text34}}</div>
                                  <div @click="handelCancel(scope.row.id)" class="operation-item"
                                    v-if="scope.row.status === 'review'">{{lang.finance_text35}}</div>
                                  <div class="operation-item" @click="handelPreview(scope.row.id)"
                                    v-if="scope.row.status === 'complete' || scope.row.status === 'wait_mail'">
                                    {{lang.invoice_text41}}
                                  </div>
                                  <div @click="handelDownload(scope.row.id)" class="operation-item"
                                    v-if="scope.row.status === 'complete' || scope.row.status === 'wait_mail'">
                                    {{lang.finance_text36}}
                                  </div>
                                  <div @click="handelMail(scope.row.id)" class="operation-item"
                                    v-if="scope.row.status === 'complete' && !scope.row.post_number">
                                    {{lang.finance_text37}}
                                  </div>
                                  <div class="operation-item"
                                    v-if="scope.row.status === 'reject' || scope.row.status === 'cancel'">--</div>
                                </div>
                              </el-popover>
                            </template>
                          </el-table-column>
                        </el-table>
                        <pagination :page-data="params4" @sizechange="sizeChange4" @currentchange="currentChange4">
                        </pagination>
                      </div>
                    </div>
                  </el-tab-pane>
                  <el-tab-pane :label="lang.finance_text38" name="6"
                    v-if="isShowCredit && Object.keys(creditData).length > 0">
                    <div class="credit-content">
                      <div class="credit-top">
                        <div class="credit-item">
                          <div class="item-top">
                            <div class="item-l">{{lang.finance_text39}}({{commonData.currency_suffix}})</div>
                            <div class="item-r"
                              v-if="creditData.status === 'Active' || creditData.status === 'Suspended'">
                              {{creditData.end_time | formateTime3}}{{lang.finance_text40}}
                            </div>
                            <div class="item-r" v-else>{{lang.finance_credit1}}</div>
                          </div>
                          <div class="item-bottom">
                            <div class="item-bl">
                              {{commonData.currency_prefix}}{{creditData.credit_limit | formatNumber}}
                            </div>
                          </div>
                        </div>
                        <div class="credit-item">
                          <div class="item-top">
                            <div class="item-l">{{lang.finance_text41}}({{commonData.currency_suffix}})</div>
                            <div class="item-r" v-if="creditData.status !== 'Expired'">
                              <span class="label-box"
                                :class="creditData.status !== 'Active' ? 'no-active' : 'is-active'">{{credit_status[creditData.status]}}</span>
                            </div>
                          </div>
                          <div class="item-bottom">
                            <div class="item-bl">
                              {{commonData.currency_prefix}}{{creditData.remaining_amount | formatNumber}}
                            </div>
                          </div>
                        </div>
                        <div class="credit-item">
                          <div class="item-top" style="align-items: start;">
                            <div class="item-l">{{lang.finance_text42}}({{commonData.currency_suffix}})</div>
                            <div class="item-r" style="text-align: right;">
                              <div>{{lang.finance_text43}}：{{commonData.currency_prefix}}{{creditData.used |
                                formatNumber}}</div>
                              <div v-if="creditData.account?.repayment_time">
                                {{lang.finance_text44}}：{{creditData.account?.repayment_time | formateTime3}}
                              </div>
                            </div>
                          </div>
                          <div class="item-bottom flex-bottom">
                            <div class="item-bl">
                              {{commonData.currency_prefix}}{{creditData.account?.status === 'Repaid' ? '0.00' : creditData.account?.amount | formatNumber}}
                            </div>
                            <el-button class="no-btn" v-if="creditData.account?.status === 'Outstanding'">
                              {{lang.finance_text45}}</el-button>
                            <el-button class="credit-btn"
                              v-if="creditData.account?.status === 'Outstanding' && creditData.account?.amount * 1 > 0"
                              @click="handlePre()">
                              {{lang.finance_credit2}}
                            </el-button>
                            <el-button class="credit-btn"
                              v-if="creditData.account?.status !== 'Repaid' &&  creditData.account?.status !== 'Outstanding'"
                              @click="handelPayCredit(creditData.account?.order_id)">{{lang.finance_text46}}</el-button>
                          </div>
                        </div>
                      </div>
                      <div class="tabledata">
                        <el-table v-loading="loading6" :data="dataList6" style="width: 100%;margin-bottom: .2rem;">
                          <el-table-column :label="lang.finance_text47" width="500" align="left">
                            <template slot-scope="scope">
                              {{scope.row.start_time | formateTime2}}- {{scope.row.end_time | formateTime2}}
                            </template>
                          </el-table-column>
                          <el-table-column :label="lang.finance_text48" align="left" :show-overflow-tooltip="true">
                            <template slot-scope="scope">
                              <span>{{commonData.currency_prefix}}{{scope.row.amount | formatNumber}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column :label="lang.finance_label4" width="200" align="left">
                            <template slot-scope="scope">
                              <span class="contract-status"
                                :style="{'color':creditStatusObj[scope.row.status].color,'background':creditStatusObj[scope.row.status].background}">{{creditStatusObj[scope.row.status].label}}</span>
                            </template>
                          </el-table-column>
                          <el-table-column :label="lang.finance_label6" width="100" align="left">
                            <template slot-scope="scope">
                              <el-popover placement="top-start" trigger="hover">
                                <i slot="reference" class="el-icon-more"></i>
                                <div class="operation-box">
                                  <div class="operation-item" @click="handelCredit(scope.row.id)">
                                    {{lang.finance_text49}}
                                  </div>
                                  <div class="operation-item" @click="handlePre(scope.row)"
                                    v-if="scope.row.status === 'Outstanding' && creditData.account?.amount * 1 > 0">
                                    {{lang.finance_credit2}}
                                  </div>
                                  <div class="operation-item" @click="handelPayCredit(scope.row?.order_id)"
                                    v-if="scope.row.status === 'Disbursed' || scope.row.status === 'Overdue'">
                                    {{lang.finance_text50}}
                                  </div>
                                </div>
                              </el-popover>
                            </template>
                          </el-table-column>
                        </el-table>
                        <pagination :page-data="params6" @sizechange="sizeChange6" @currentchange="currentChange6">
                        </pagination>
                      </div>
                    </div>
                  </el-tab-pane>
                </el-tabs>
              </div>
            </div>
          </div>
        </el-main>
        <!-- 删除确认 dialog -->
        <el-dialog width="4.35rem" :visible.sync="isShowDeOrder" class="delete-order-dialog" :show-close=false
          @close="isShowDeOrder=false">
          <div class="delete-box">
            <div class="delete-content">{{isBatchDel ? lang.finance_text148 : lang.finance_text7}}</div>
            <div class="delete-btn">
              <el-button class="save-btn" @click="suerDelOrder" :loading="oderDelLoading">{{lang.finance_btn8}}
              </el-button>
              <el-button class="cancel-btn" @click="isShowDeOrder=false">{{lang.finance_btn7}}</el-button>
            </div>
          </div>
        </el-dialog>
        <!-- 支付弹窗 -->
        <pay-dialog ref="payDialog" :allow-credit="useCredit" @payok="paySuccess" @paycancel="payCancel"></pay-dialog>
        <!-- 充值弹窗 -->
        <recharge-dialog ref="rechargeDialog" @success="rechargeSuccess"></recharge-dialog>
        <!-- 提现弹窗 -->
        <withdraw-dialog ref="withdrawDialog" @dowithdraw="dowithdraw"></withdraw-dialog>
        <!-- 快递信息弹窗 -->
        <el-dialog width="6.8rem" :visible.sync="isShowKd" :show-close="false" @close="kdClose" class="kd-dialog">
          <div class="dialog-title">{{lang.finance_text51}}</div>
          <div class="dialog-dec">{{lang.finance_text52}}</div>
          <div class="dialog-box">
            <div class="kd-item"><span class="kd-label">{{lang.finance_text53}}:</span><span
                class="kd-value">{{recData.courier_company}}</span></div>
            <div class="kd-item"><span class="kd-label">{{lang.finance_text54}}:</span><span
                class="kd-value">{{recData.post_number}}</span></div>
            <div class="kd-item"><span class="kd-label">{{lang.finance_text55}}:</span><span
                class="kd-value">{{recData.rec_address}}</span></div>
            <div class="kd-item"><span class="kd-label">{{lang.finance_text56}}:</span><span
                class="kd-value">{{recData.rec_phone}}</span></div>
            <div class="kd-item"><span class="kd-label">{{lang.finance_text57}}:</span><span
                class="kd-value">{{recData.rec_person}}</span></div>
          </div>
          <div class="dialog-fotter">
            <el-button @click="kdClose">{{lang.finance_text58}}</el-button>
          </div>
        </el-dialog>
        <!-- 甲方信息管理弹窗 -->
        <contract-info ref="contractInfo"></contract-info>
        <!-- 取消申请弹窗 -->
        <el-dialog width="4.5rem" :visible.sync="isShowCancel" :show-close="false" @close="cancelClose"
          class="cancel-dialog">
          <div class="dialog-title">{{lang.finance_text72}}</div>
          <div class="dialog-dec">{{lang.finance_text73}}</div>
          <div class="dialog-fotter">
            <el-button class="save-btn" @click="saveCancel">{{lang.finance_text74}}</el-button>
            <el-button class="cancel-btn" @click="cancelClose">{{lang.finance_text75}}</el-button>
          </div>
        </el-dialog>
        <!-- 申请纸质合同弹窗 -->
        <el-dialog width="6.8rem" :visible.sync="isShowMailDia" :show-close="false" @close="MailClose"
          class="mail-dialog">
          <div class="dialog-title">{{lang.finance_text76}}</div>
          <div class="dialog-dec">
            {{lang.finance_text77}}
          </div>
          <div class="dialog-box">
            <el-form :model="mailFormData" class="info-form" :rules="mailRules" ref="mailForm" label-position="top">
              <el-form-item :label="lang.finance_text78" prop="rec_person">
                <el-input v-model="mailFormData.rec_person" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
              <el-form-item :label="lang.finance_text79" prop="rec_address">
                <el-input v-model="mailFormData.rec_address" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
              <el-form-item :label="lang.finance_text80" prop="rec_phone">
                <el-input v-model="mailFormData.rec_phone" :placeholder="lang.finance_text65"></el-input>
              </el-form-item>
            </el-form>
          </div>
          <div class="dialog-fotter">
            <div class="fotter-left">
              {{lang.finance_text81}}：<span class="price-blue">￥20.00</span>
            </div>
            <div style="display: flex;">
              <el-button class="save-btn" @click="saveMailData">{{lang.finance_text82}}</el-button>
              <el-button class="cancel-btn" @click="MailClose">{{lang.finance_text83}}</el-button>
            </div>
          </div>
        </el-dialog>
        <!-- 消费记录弹窗 -->
        <el-dialog width="12rem" :visible.sync="isShowCreditDia" :show-close="false" @close="creditClose"
          class="mail-dialog creat-dia">
          <div class="dialog-title">{{lang.finance_text84}}</div>
          <div class="dialog-tips dialog-dec">
            <div v-for="(item,index) in tipslist1" class="tips_item" :key="index">
              <span class="dot" :style="{'background':item.color}"></span>
              <span>{{item.name}}</span>
            </div>
          </div>
          <div class="dialog-box" style="margin-top: 0.2rem;">
            <el-table v-loading="loading7" :data="dataList7" style="width: 100%;margin-bottom: 20px;"
              :row-key="getRowKey" lazy :load="load" :tree-props="{children: 'children', hasChildren: 'hasChildren'}">
              <el-table-column prop="id" label="ID" width="90" align="left">
                <template slot-scope="scope">
                  <span class="a-text" @click="goOrderDetail(scope.row.id)">
                    {{scope.row.product_names ? scope.row.id : '--'}}</span>
                </template>
              </el-table-column>
              <el-table-column prop="product_names" :label="lang.finance_label1" min-width="250"
                :show-overflow-tooltip="true">
                <template slot-scope="scope">
                  <span class="dot" :class="scope.row.type"></span>
                  <el-tooltip placement="top" v-if="scope.row.description">
                    <div slot="content">{{scope.row.description}}</div>
                    <span style="cursor: pointer;" class="a-text"
                      @click="goOrderDetail(scope.row.id)">{{scope.row.product_name}}</span>
                  </el-tooltip>
                  <span v-else class="a-text" @click="goOrderDetail(scope.row.id)">{{scope.row.product_name}}</span>
                </template>
              </el-table-column>
              <el-table-column prop="billing_cycle" :label="lang.finance_label2" width="150">
                <template slot-scope="scope">
                  <span>{{ commonData.currency_prefix + scope.row.amount}}</span>
                  <span v-if="scope.row.billing_cycle">/{{scope.row.billing_cycle}}</span>
                </template>
              </el-table-column>
              <el-table-column prop="pay_time" :label="lang.finance_text85" width="200">
                <template slot-scope="scope">
                  <span>{{scope.row.pay_time | formateTime}}</span>
                </template>
              </el-table-column>
              <el-table-column prop="status" :label="lang.finance_label4" width="150">
                <template slot-scope="scope">
                  <!-- 未付款 -->
                  <el-tag v-if="scope.row.status && scope.row.status=='Unpaid'" style="cursor: pointer;" class="Unpaid">
                    {{lang.finance_text3}}
                  </el-tag>
                  <!-- 已付款 -->
                  <el-tag v-if="scope.row.status && scope.row.status=='Paid'" class="Paid">
                    {{lang.finance_text4}}
                  </el-tag>
                  <!-- 已完成 -->
                  <el-tag v-if="scope.row.status && scope.row.status=='Refunded'" class="Refunded">
                    {{lang.finance_text17}}
                  </el-tag>
                  {{scope.row.host_status?status[scope.row.host_status]:null}}
                  {{scope.row.host_status || scope.row.status ? null : '--'}}
                </template>
              </el-table-column>
              <el-table-column prop="gateway" :label="lang.finance_label5" width="180">
                <template slot-scope="scope">
                  <!-- 存在支付状态 父 -->
                  <div v-if="scope.row.status">
                    <!-- 已支付 -->
                    <div v-if="scope.row.gateway">
                      <!-- 使用余额 -->
                      <div v-if="scope.row.credit > 0">
                        <!-- 全部使用余额 -->
                        <div v-if="scope.row.credit == scope.row.amount">
                          <span>{{lang.finance_text5}}</span>
                        </div>
                        <!-- 部分使用余额 -->
                        <div v-else>
                          <el-popover placement="top" trigger="hover" popper-class="tooltip">
                            <i class="el-icon-s-finance" style="color: var(--color-warning);font-size: 0.35rem;"></i>
                            <span style="color: var(--color-warning);">
                              {{commonData.currency_prefix + scope.row.credit + commonData.currency_suffix}}</span>
                            <span slot="reference" class='gateway-pay'>{{lang.finance_text5}}</span>
                          </el-popover>
                          <span>{{scope.row.gateway ? '+'+scope.row.gateway:''}}</span>
                        </div>
                      </div>
                      <!-- 未使用余额 -->
                      <span v-else>{{scope.row.gateway}}</span>

                    </div>
                    <!-- 未支付 -->
                    <a v-else class='gateway-pay'>--</a>
                  </div>

                </template>
              </el-table-column>
            </el-table>
            <pagination :page-data="params7" @sizechange="sizeChange7" @currentchange="currentChange7"></pagination>
          </div>
          <div class="dialog-fotter">
            <div></div>
            <el-button class="save-btn" @click="creditClose">{{lang.finance_text86}}</el-button>
          </div>
        </el-dialog>
        <!-- 提前还款弹窗 -->
        <el-dialog width="6.8rem" :visible.sync="isShowPre" :show-close="false" @close="preClose"
          class="kd-dialog pre-dialog">
          <div class="dialog-title">{{lang.finance_credit2}}</div>
          <div class="dialog-box">
            <p class="tit">{{lang.finance_credit3}}</p>
            <div class="con">
              <p class="item">
                {{lang.finance_credit4}}： {{preData.start_time | formateTime2}}- {{preData.end_time | formateTime2}}
              </p>
              <p class="item">
                {{lang.finance_credit5}}：{{commonData.currency_prefix}}{{preData.amount | formatNumber}}
              </p>
            </div>
          </div>
          <div class="dialog-fotter">
            <el-button class="save-btn" @click="submitPre" :loading="submitLoading">{{lang.finance_btn8}}</el-button>
            <el-button class="cancel-btn" @click="preClose">{{lang.finance_text58}}</el-button>
          </div>
          <p class="s-tip">{{lang.finance_credit6}}</p>
        </el-dialog>
        <!-- 冻结记录弹窗 -->
        <el-dialog width="9.8rem" :visible.sync="isShowFreezeDia" :show-close="false" @close="freezeClose"
          class="mail-dialog creat-dia">
          <div class="dialog-title">{{lang.finance_text141}}</div>
          <div class="dialog-box" style="margin-top: 0.2rem;">
            <el-table v-loading="freezeLoading" :data="freezeList" style="width: 100%;margin-bottom: 20px;">
              <el-table-column prop="id" label="ID" width="90" align="left">
                <template slot-scope="scope">
                  <span>
                    {{scope.row.id}}
                  </span>
                </template>
              </el-table-column>
              <el-table-column prop="amount" :label="lang.finance_text143">
                <template slot-scope="scope">
                  <span>{{ commonData.currency_prefix + scope.row.amount}}</span>
                </template>
              </el-table-column>
              <el-table-column prop="create_time" :label="lang.finance_text142">
                <template slot-scope="scope">
                  <span>{{scope.row.create_time | formateTime}}</span>
                </template>
              </el-table-column>
              <el-table-column prop="client_notes" :label="lang.finance_text144" width="150" show-overflow-tooltip>
                <template slot-scope="scope">
                  <span>{{ scope.row.client_notes || '--'}}</span>
                </template>
              </el-table-column>
            </el-table>
          </div>
          <div class="dialog-fotter">
            <div></div>
            <el-button class="save-btn" @click="freezeClose">{{lang.finance_text86}}</el-button>
          </div>
        </el-dialog>
        <!-- 凭证 -->
        <proof-dialog ref="proof" @refresh="refresh"></proof-dialog>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/api/finance.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/finance.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/rechargeDialog/rechargeDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/withdrawDialog/withdrawDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/contractInfo/contractInfo.js"></script>

  {include file="footer"}

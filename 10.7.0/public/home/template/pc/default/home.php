<link rel="stylesheet" href="/{$template_catalog_home}/template/{$themes_home}/css/home.css">
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
  <div class="template">
    <el-container>
      <aside-menu></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main class="home-main">
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="main-content">
              <div class="left-box">
                <div class="info-box">
                  <div class="info-first" @click="goUser" v-loading="nameLoading">
                    <div class="name-first" ref="headBoxRef">
                      {{account.firstName}}
                    </div>
                    <div class="name-box">
                      <p class="hello" :title="account.username">
                        {{lang.index_hello}},{{account.username}}
                        <span v-if="idcsmart_client_level.id"
                          :style="{'color':idcsmart_client_level.background_color}">({{idcsmart_client_level.name}})
                        </span>
                      </p>
                      <p class="name">
                        ID：<span class="id-text">{{account.id}}</span>
                      </p>
                    </div>
                  </div>
                  <el-divider class="divider-box" direction="vertical"></el-divider>
                  <div class="info-second" v-loading="nameLoading">
                    <div class="email-box">
                      <span><img src="/{$template_catalog_home}/template/{$themes}/img/email-icon.png"
                          alt="">{{lang.index_email}}</span>
                      <span class="phone-number">{{account.email ? account.email : '--'}}</span>
                    </div>
                    <div class="phone-box">
                      <span><img src="/{$template_catalog_home}/template/{$themes}/img/tel-icon.png"
                          alt="">{{lang.index_tel}}</span>
                      <span class="phone-number">{{account.phone ? account.phone : '--'}}</span>
                    </div>
                  </div>
                  <el-divider class="divider-box" direction="vertical"></el-divider>
                  <div class="info-three" v-plugin="'IdcsmartCertification'"
                    v-if="certificationObj.certification_open === 1">
                    <div class="compny-box">
                      <div class="left-icon">
                        <img src="/{$template_catalog_home}/template/{$themes}/img/compny-icon.png" alt="">
                        <span class="left-type">{{lang.index_compny}}</span>
                      </div>
                      <div class="right-text">
                        <div class="right-title">
                          <span class="company-name"
                            v-if="certificationObj.company?.status === 1">{{certificationObj.company.certification_company}}</span>
                          <span class="company-name bule-text" @click="handelAttestation"
                            v-else>{{lang.index_goAttestation}}</span>
                        </div>
                        <div class="certify-id" v-if="certificationObj.certification_show_certify_id === 1">
                          <div class="right-type">{{lang.finance_custom23}}：</div>
                          <div class="company-name certify-bottom" :title="certificationObj.company?.certify_id">
                            <span
                              class="certify-text">{{certificationObj.company?.certify_id ? certificationObj.company.certify_id : '--'}}</span>
                            <img class="cpoy-btn" v-copy="certificationObj.company.certify_id"
                              v-if="certificationObj.company?.certify_id"
                              src="/{$template_catalog_home}/template/{$themes}/img/copy.svg" alt="">
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="person-box">
                      <div class="left-icon">
                        <img class="left-icon" src="/{$template_catalog_home}/template/{$themes}/img/person-icon.png"
                          alt="">
                        <span class="left-type">{{lang.index_name}}</span>
                      </div>
                      <div class="right-text">
                        <div class="right-title">
                          <span class="company-name" v-if="certificationObj.is_certification"
                            :title="certificationObj.company.status === 1 ? certificationObj.company.card_name : certificationObj.person.card_name">
                            {{certificationObj.company.status === 1 ? certificationObj.company.card_name : certificationObj.person.card_name}}
                          </span>
                          <span class="company-name bule-text" @click="handelAttestation"
                            v-else>{{lang.index_goAttestation}}</span>
                        </div>
                        <div class="certify-id" v-if="certificationObj.certification_show_certify_id === 1">
                          <div class="right-type">{{lang.finance_custom24}}：</div>
                          <div class="company-name certify-bottom" :title="certificationObj.person?.certify_id">
                            <span
                              class="certify-text">{{certificationObj.person?.certify_id ? certificationObj.person.certify_id : '--'}}
                            </span>
                            <img v-copy="certificationObj.person?.certify_id" v-if="certificationObj.person?.certify_id"
                              class="cpoy-btn" src="/{$template_catalog_home}/template/{$themes}/img/copy.svg" alt="">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="statistics-box">
                  <div class="statistics-content" v-loading="nameLoading">
                    <div class="money-box">
                      <div class="money-top">
                        <div class="money-credit">
                          <div class="credit-btn" @click="showCz" v-if="commonData.recharge_open == 1">
                            {{lang.index_text2}}
                          </div>
                          <div class="credit-title" v-if="commonData.balance_notice_show == 1">
                            <div class="credit-name">{{lang.index_text3}}</div>
                            <div class="create-notice">
                              <div class="notice-status" :class="{'active': account.credit_remind === 1}"></div>
                              <div class="notice-btn" @click="setAccoutCredit">【{{lang.coin_text66}}】</div>
                            </div>
                          </div>
                          <div class="voucher-box" v-if="voucherList.length > 0">
                            {{lang.index_text24}}
                            <a href="/finance.htm?tab=4" target="_blank" class="bule-text">
                              {{lang.index_text25}}
                            </a>
                          </div>
                          <div class="credit-money">
                            <div class="credit-num">
                              <span class="s-24">{{commonData.currency_prefix}}</span>{{account.credit}}
                              <span v-if="commonData.recharge_open == 1 && coinRecharge.length > 0"
                                class="recharge-text" @click="showCz">{{lang.index_text35}}</span>
                            </div>
                          </div>
                        </div>
                        <div class="money-credit" v-plugin="'Coin'" v-if="coinData.name">
                          <div class="credit-title coin-title">
                            <div class="credit-name" style="display: flex; align-items: center;">
                              {{coinData.name}}
                              <el-tooltip effect="dark" placement="top" v-if="coinData.coin_description_open == 1">
                                <div slot="content" v-html="coinData.coin_description"></div>
                                <svg t="1745803081479" viewBox="0 0 1024 1024" version="1.1"
                                  xmlns="http://www.w3.org/2000/svg" p-id="14138" width="16" height="16"
                                  xmlns:xlink="http://www.w3.org/1999/xlink">
                                  <path
                                    d="M512 97.52381c228.912762 0 414.47619 185.563429 414.47619 414.47619s-185.563429 414.47619-414.47619 414.47619S97.52381 740.912762 97.52381 512 283.087238 97.52381 512 97.52381z m0 73.142857C323.486476 170.666667 170.666667 323.486476 170.666667 512s152.81981 341.333333 341.333333 341.333333 341.333333-152.81981 341.333333-341.333333S700.513524 170.666667 512 170.666667z m45.32419 487.619047v73.142857h-68.510476l-0.024381-73.142857h68.534857z m-4.047238-362.008381c44.251429 8.923429 96.889905 51.126857 96.889905 112.518096 0 61.415619-50.151619 84.650667-68.120381 96.134095-17.993143 11.50781-24.722286 24.771048-24.722286 38.863238V609.52381h-68.534857v-90.672762c0-21.504 6.89981-36.571429 26.087619-49.883429l4.315429-2.852571 38.497524-25.6c24.551619-16.530286 24.210286-49.712762 9.020952-64.365715a68.998095 68.998095 0 0 0-60.391619-15.481904c-42.715429 8.387048-47.640381 38.521905-47.932952 67.779047v16.554667H390.095238c0-56.953905 6.534095-82.773333 36.912762-115.395048 34.03581-36.449524 81.993143-42.300952 126.268952-33.328762z"
                                    p-id="14139" fill="currentColor"></path>
                                </svg>
                              </el-tooltip>
                            </div>
                            <a href="/finance.htm?tab=7" target="_blank" class="credit-detail">
                              {{lang.index_text34}}
                            </a>
                          </div>
                          <div class="credit-money">
                            <div class="credit-num">
                              <span class="s-24">{{commonData.currency_prefix}}</span>{{coinData.leave_amount}}
                            </div>
                          </div>
                        </div>
                      </div>
                      <template v-if="isShowCredit && creditData.status">
                        <div class="money-order">
                          <div class="money-order-item">
                            <span
                              class="money-order-title">{{lang.finance_text42}}({{commonData.currency_suffix}})</span>
                            <span class="money-order-value"><span
                                class="s-12">{{commonData.currency_prefix}}</span>{{creditData.account?.status === 'Repaid' ? '0.00' : creditData.account?.amount}}</span>
                          </div>
                          <div class="money-order-divider"></div>
                          <div class="money-order-item">
                            <div class="money-order-title">
                              {{lang.finance_text38}}
                              <div class="credit-tag" v-if="creditData.status === 'Expired'">{{lang.finance_text93}}
                              </div>
                              <div class="credit-tag" v-if="creditData.status === 'Overdue'">{{lang.finance_text94}}
                              </div>
                              <div class="credit-tag" v-if="creditData.status === 'Active'">{{lang.finance_text95}}
                              </div>
                              <div class="credit-tag" v-if="creditData.status === 'Suspended'">{{lang.finance_text96}}
                              </div>
                            </div>
                            <div class="money-order-value">
                              <span class="s-12">{{commonData.currency_prefix}}</span>{{creditData.remaining_amount}}
                            </div>
                          </div>
                        </div>
                      </template>

                    </div>
                    <div class="order-box">
                      <div class="order-item order-box-1" @click="goProductList('using')">
                        <div class="order-type-img">
                          <img src="/{$template_catalog_home}/template/{$themes}/img/activation-icon.png" alt="">
                        </div>
                        <h3 class="order-title">{{lang.index_text6}}</h3>
                        <div class="order-nums">{{account.host_active_num}}</div>
                      </div>
                      <div class="order-item order-box-2" @click="goProductList()">
                        <div class="order-type-img">
                          <img src="/{$template_catalog_home}/template/{$themes}/img/prduct-icon.png" alt="">
                        </div>
                        <h3 class="order-title">{{lang.index_text7}}</h3>
                        <div class="order-nums">{{account.host_num}}</div>
                      </div>
                      <div class="order-item order-box-3" @click="goOrderList('Unpaid')">
                        <div class="order-type-img">
                          <img src="/{$template_catalog_home}/template/{$themes}/img/no-pay-order.png" alt="">
                        </div>
                        <h3 class="order-title">{{lang.index_text8}}</h3>
                        <div class="order-nums">{{account.unpaid_order}}</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="statistics-bottom">
                  <div class="statistics-item">
                    <div class="statistics-item-name">
                      <span>{{lang.index_text4}}({{commonData.currency_suffix}})</span>
                      <span
                        :class="Number(account.this_month_consume_percent) >= 0 ? 'green-text' : 'red-text'"><span></span>{{Number(account.this_month_consume_percent)}}%</span>
                    </div>
                    <div class="statistics-item-value">
                      <span class="s-12">{{commonData.currency_prefix}}</span>{{account.this_month_consume}}
                    </div>
                  </div>

                  <div class="statistics-item-divider"></div>

                  <div class="statistics-item">
                    <div class="statistics-item-name">
                      <span>{{lang.index_text5}}({{commonData.currency_suffix}})</span>
                    </div>
                    <div class="statistics-item-value">
                      <span class="s-12">{{commonData.currency_prefix}}</span>{{account.consume}}
                    </div>
                  </div>

                </div>
                <div class="product-list-box">
                  <h3 class="title-text">{{lang.index_text9}}</h3>
                  <el-table :data="productList" style="width: 100%" v-if="productList.length !== 0"
                    v-loading="productListLoading">
                    <el-table-column prop="product_name" :label="lang.index_text10">
                      <template slot-scope="{row}">
                        <a :href="`/productdetail.htm?id=${row.id}`" class="product-name"
                          target="_blank">{{row.product_name}}</a>
                      </template>
                    </el-table-column>
                    <el-table-column prop="name" :label="lang.index_text12">
                      <template slot-scope="{row}">
                        <span>{{row.name}}</span>
                      </template>
                    </el-table-column>
                    <el-table-column prop="due_time" :label="lang.index_text13">
                      <template slot-scope="{row}">
                        <span :class="row.isOverdue ? 'red-time' : ''">{{row.due_time | formateTime}}</span>
                      </template>
                    </el-table-column>
                    <el-table-column prop="client_notes" :label="lang.invoice_text139" show-overflow-tooltip>
                      <template slot-scope="{row}">
                        <span>{{row.client_notes || '--'}}</span>
                      </template>
                    </el-table-column>
                  </el-table>
                  <div v-if="productList.length === 0 && !productListLoading" class="no-product">
                    <h2>{{lang.index_text14}}</h2>
                    <p>{{lang.index_text15}}</p>
                    <el-button @click="goGoodsList" type="primary">{{lang.index_text16}}</el-button>
                  </div>
                </div>
              </div>
              <div class="right-box">
                <!-- 推介计划开始 -->

                <div class="recommend-box-open" v-if="showRight && isOpen" v-plugin="'IdcsmartRecommend'">
                  <div class="recommend-top">
                    <div class="left">
                      <div class="row1">
                        <div class="title-text">{{lang.referral_title1}}</div>
                        <span class="reword" @click="toReferral"><img
                            src="/{$template_catalog_home}/template/{$themes}/img/reword.png"
                            alt="">{{lang.referral_text14}}</span>
                      </div>
                      <div class="row2">{{lang.referral_title6}}</div>
                      <div class="row3">{{lang.referral_text15}}</div>
                      <div class="row4">{{lang.referral_text16}}</div>
                    </div>
                    <img class="right" src="/{$template_catalog_home}/template/{$themes}/img/credit-card.png" alt="">
                  </div>
                  <div class="url">
                    <div class="url-text" :title="promoterData.url">{{promoterData.url}}</div>
                    <div class="copy-btn" @click="copyUrl(promoterData.url)">{{lang.referral_btn2}}</div>
                  </div>
                  <div class="top-statistic">
                    <div class="top-item">
                      <div class="top-money">{{commonData.currency_prefix}}{{promoterData.withdrawable_amount}}</div>
                      <div class="top-text">{{lang.referral_title2}}</div>
                    </div>
                    <div class="top-item">
                      <div class="top-money">{{commonData.currency_prefix}}{{promoterData.pending_amount}}
                      </div>
                      <div class="top-text">{{lang.referral_title4}}</div>
                    </div>
                  </div>
                </div>
                <div class="recommend-box" v-if="!showRight || !isOpen">
                  <img src="/{$template_catalog_home}/template/{$themes}/img/recommend-img.png" alt="">
                  <div v-if="showRight">
                    <h2>{{lang.index_text17}}</h2>
                    <p>{{lang.index_text18}}</p>
                    <div class="no-recommend" @click="openVisible = true">{{lang.index_text28}}</div>
                  </div>
                  <div v-else class="recommend-text">{{lang.index_text21}}</div>
                </div>
                <!-- 推介计划结束 -->

                <div class="WorkOrder-box" v-if="ticketList.length !==0 " v-plugin="'IdcsmartTicket'">
                  <div class="title-text WorkOrder-title">
                    <div>{{lang.index_text22}}</div>
                    <div class="more" @click="goWorkPage">···</div>
                  </div>
                  <div class="WorkOrder-content">
                    <div class="WorkOrder-item" v-for="item in ticketList" :key="item.id"
                      @click="goTickDetail(item.id)">
                      <div class="replay-div" :style="{'background':`${item.color}`}">{{item.status}}</div>
                      <div class="replay-box">
                        <div class="replay-title">#{{item.ticket_num}} - {{item.title}}</div>
                        <div class="replay-name">{{item.name}}</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="notice-box" v-if="homeNewList.length !==0" v-plugin="'IdcsmartNews'">
                  <div class="title-text WorkOrder-title">
                    <div>{{lang.index_text23}}</div>
                    <div class="more" @click="goNoticePage">···</div>
                  </div>
                  <div class="WorkOrder-content">
                    <div v-for="item in homeNewList" :key="item.id" class="notice-item"
                      @click="goNoticeDetail(item.id)">
                      <div class="notice-item-left">
                        <h3 class="notice-time">{{item.create_time | formareDay}}</h3>
                        <h4 class="notice-title">{{item.title}}</h4>
                        <h5 class="notice-type">{{item.type}}</h5>
                      </div>
                      <div class="notice-item-right"><i class="el-icon-arrow-right"></i></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- 确认开启弹窗 -->
          <el-dialog :title="lang.referral_title8" :visible.sync="openVisible" width="4.8rem"
            custom-class="open-dialog">
            <span>{{lang.referral_tips7}}</span>
            <span slot="footer" class="dialog-footer">
              <el-button class="btn-ok" type="primary" @click="openReferral">{{lang.referral_btn6}}</el-button>
              <el-button class="btn-no" @click="openVisible = false">{{lang.referral_btn7}}</el-button>
            </span>
          </el-dialog>
          <pay-dialog ref="payDialog" @payok="paySuccess"></pay-dialog>
          <credit-notice ref="creditNotice" @success="paySuccess"></credit-notice>
          <recharge-dialog ref="rechargeDialog" @success="rechargeSuccess"></recharge-dialog>
          <!-- 微信公众号 -->
          <div class="wx-code" v-if="hasWxPlugin && conectInfo.is_subscribe === 0">
            <el-popover width="200" trigger="hover" @show="getWxcode" placement="left">
              <div class="wx-box">
                <p class="tit">{{lang.wx_tip1}}</p>
                <div class="img" v-loading="codeLoading">
                  <img :src="wxQrcode" alt="" v-if="wxQrcode">
                </div>
              </div>
              <div class="wx-img" slot="reference"></div>
            </el-popover>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/components/creditNotice/creditNotice.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/rechargeDialog/rechargeDialog.js"></script>
  <script src="/{$template_catalog_home}/template/{$themes_home}/api/finance.js"></script>
  <script src="/{$template_catalog_home}/template/{$themes_home}/api/home.js"></script>
  <script src="/{$template_catalog_home}/template/{$themes_home}/js/home.js"></script>

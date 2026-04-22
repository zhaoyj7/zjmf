<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog_cart}/template/{$themes_cart}/css/goodsList.css">
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
      <aside-menu @getruleslist="getRule"></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div v-if="commonData.cart_instruction == 1 && commonData.cart_instruction_content" class="cart-des"
              v-html="commonData.cart_instruction_content">
            </div>
            <div class="main-title" v-else>{{lang.new_goods}}</div>
            <div class="main-content-box">
              <div class="search-box">
                <el-select v-model="select_first_obj.id" :placeholder="lang.first_level" @change="selectFirstType">
                  <el-option v-for="item in first_group_list" :key="item.id " :label="item.name" :value="item.id">
                  </el-option>
                </el-select>
                <el-select v-model="select_second_obj.id" :placeholder="lang.second_level" :disabled="secondLoading"
                  :loading="secondLoading" @change="selectSecondType" class="second-select">
                  <el-option v-for="item in second_group_list" :key="item.name " :label="item.name" :value="item.id">
                  </el-option>
                </el-select>
                <el-input :placeholder="lang.goods_search_placeholder" v-if="!isDomain" clearable v-model="searchValue"
                  class="search-input" @keyup.enter.native="searchGoods"></el-input>
                <el-button class="search-btn" type="primary" key="ddd" @click="searchGoods" :loading="searchLoading"
                  v-if="!isDomain">{{lang.search}}</el-button>
              </div>
              <div class="second-desc" v-if="select_second_obj.description">
                <scroll-text mode="loop">
                  {{select_second_obj.description}}
                </scroll-text>
              </div>
              <div class="shopping-box" v-loading="goodSLoading">
                <template v-if="!isDomain">
                  <div class="no-goods" v-if="goodsList.length === 0 && !goodSLoading">
                    <el-empty :description="lang.no_goods"></el-empty>
                  </div>
                  <div v-else class="goods-list-div">
                    <template v-for="(item,index) in goodsList">
                      <div class="shopping-item">
                        <div class="client-box" v-if="item.client_level_name && item.client_level_name !== ''">
                          <span>{{lang.shoppingCar_tip_text15}}</span>
                        </div>
                        <div v-html="item.description" class="goods-description"></div>
                        <div class="goods-content">
                          <div class="goods-tag"
                            v-if="item.pay_ontrial && item.pay_ontrial?.status === 1 || item.aodun_firewall_product">
                            <div class="tag-item" v-if="item.pay_ontrial && item.pay_ontrial?.status === 1 ">
                              {{lang.support_trial}}
                            </div>
                            <div class="tag-item" v-if="item.aodun_firewall_product">
                              {{lang.firewall_text1}}
                            </div>
                          </div>
                          <div class="goods-name" :class="{'sold-out':item.stock_control === 1 && item.qty <= 0}">
                            <div class="goods-name-text">{{ item.name }}</div>
                            <div class="qty-box" v-if="item.stock_control === 1">
                              <span v-if="item.qty > 0">{{lang.stock}}：<span
                                  class="stock-num">{{item.qty}}</span></span>
                              <img src="/{$template_catalog_cart}/template/{$themes_cart}/img/sold_out.svg" alt=""
                                v-else>
                            </div>
                          </div>
                          <div class="goods-active">
                            <template v-if="item.activeList.length > 0 || item.addon_coin === 1">
                              <div class="active-name" v-if="item.addon_coin === 1">
                                {{lang.coin_text10}}{{item.addon_coin_name}}
                              </div>
                              <template v-for="active in item.activeList">
                                <el-popover placement="top-start" trigger="hover">
                                  <div class="active-item">
                                    <span v-if="active.type === 'percent'">{{lang.goods_text1}} {{active.value}}%</span>
                                    <span v-if="active.type === 'reduce'"> {{lang.goods_text2}} {{active.full}}
                                      {{lang.goods_text3}} {{active.value}}</span>
                                  </div>
                                  <div class="active-name" slot="reference">
                                    {{active.name}}
                                  </div>
                                </el-popover>
                              </template>
                            </template>
                            <template v-else>
                              <div class="active-name" style="opacity: 0;">
                                {{lang.subaccount_text55}}
                              </div>
                            </template>
                          </div>
                          <div class="price-box">
                            <div class="price-box-left">
                              <span class="item-price">
                                <span
                                  class="item-price-prefix">{{commonData.currency_prefix}}</span>{{item.price_discounted || item.price}}<span
                                  class="item-price-cycle">{{item.cycle ? '/' +  item.cycle : ''}}</span>
                              </span>
                              <span class="original-price"
                                v-if="item.price_discounted && item.price != item.price_discounted">
                                <span class="item-price-prefix">{{commonData.currency_prefix}}</span> {{item.price}}
                              </span>
                            </div>
                            <el-button :disabled="item.stock_control === 1 && item.qty <= 0" class="buy-btn"
                              type="primary" @click="goOrder(item)">{{lang.buy}}</el-button>
                          </div>
                        </div>
                      </div>
                    </template>
                  </div>
                </template>
                <template v-else>
                  <div class="domain-box">
                    <div class="register-type">
                      <span class="reg-ridio" :class="regType === '1' ? 'isActice' : ''"
                        @click="regType = '1'">{{lang.template_text93}}</span>
                      <template v-if="domainConfig.batch_search_domain === 1">
                        <el-divider direction="vertical"></el-divider>
                        <span class="reg-ridio" :class="regType === '2' ? 'isActice' : ''"
                          @click="regType = '2'">{{lang.template_text94}}</span>
                      </template>
                    </div>
                    <div class="domain-search" v-if="regType === '1'">
                      <el-input :placeholder="lang.template_text92" v-model="domainInput" clearable
                        @keyup.enter.native="handelDomainSearch">
                        <div class="suffix-box" slot="append" @click="isShowSuffixBox = !isShowSuffixBox">
                          {{selectSuffix}}
                          <i class="el-icon-arrow-down select-btn"></i>
                        </div>
                      </el-input>
                      <el-button class="search-button" @click="handelDomainSearch"
                        :loading="isSearching">{{lang.template_text95}}</el-button>
                      <div class="suffix-list" v-show="isShowSuffixBox">
                        <div class="suffix-item" @click="handelSelectSuffix(item.suffix)"
                          :class="selectSuffix === item.suffix ? 'suffix-active' : ''" v-for="item in suffixList"
                          :key="item.suffix">{{item.suffix}}</div>
                      </div>
                    </div>
                    <div class="batch-search-box" v-if="regType === '2'">
                      <div class="batch-tips" v-loading="batchLoading">
                        <el-input v-model="textarea2" resize="none" class="input-batch" type="textarea"
                          :placeholder="`${lang.template_text106}\n${lang.template_text107}${domainConfig.number_limit}${lang.template_text108}${domainConfig.number_limit}${lang.template_text109}\n${lang.template_text110}\n${lang.template_text111}`">
                        </el-input>
                      </div>
                      <div class="batch-btn">
                        <div class="upload-btn" @click="isShowUpload = true">
                          <svg t="1750672276335" class="icon" viewBox="0 0 1024 1024" version="1.1"
                            xmlns="http://www.w3.org/2000/svg" p-id="10857" width="16" height="16">
                            <path
                              d="M938.855808 638.776382l0 270.299169c0 27.41028-22.210861 49.634444-49.621141 49.634444l-754.442728 0c-27.41028 0-49.647747-22.224164-49.647747-49.634444L85.144192 638.776382c0-27.41028 22.224164-49.634444 49.634444-49.634444s49.634444 22.224164 49.634444 49.634444l0 220.664725 655.17384 0L839.58692 638.776382c0-27.41028 22.224164-49.634444 49.634444-49.634444S938.855808 611.366102 938.855808 638.776382zM349.445764 351.817788l112.918769-115.288746 0 429.77837c0 27.411303 22.224164 49.634444 49.634444 49.634444 27.41028 0 49.634444-22.223141 49.634444-49.634444L561.633421 236.534158 674.547073 351.812671c9.722432 9.927093 22.591531 14.904455 35.470863 14.904455 12.524245 0 25.071002-4.716418 34.725896-14.172791 19.583011-19.184945 19.913539-50.608631 0.733711-70.190619L547.478026 80.195483c-9.335622-9.535167-22.116717-14.905478-35.46063-14.905478-13.338796 0-26.120914 5.370311-35.456536 14.900362L278.542924 282.3486c-19.184945 19.588127-18.86465 51.010791 0.722454 70.190619C298.847365 371.724163 330.271052 371.394658 349.445764 351.817788z"
                              p-id="10858" fill="var(--color-primary)"></path>
                          </svg>
                          {{lang.template_text132}}
                        </div>
                        <el-button @click="batchSearchDomain" type="primary"
                          :loading="batchLoading">{{lang.template_text112}}</el-button>
                      </div>
                    </div>
                    <div class="domain-content">
                      <div class="domain-left">
                        <div class="domain-one" v-if="regType === '1'">
                          <div class="domain-one-list" v-if="domainList.length !==0" v-loading="isSearching">
                            <div class="search-title">
                              <span>{{lang.template_text96}}</span>
                              <span class="search-fillter is_select" @click="openFilter"><i
                                  class="el-icon-search"></i>{{isShowFilrer ? lang.wx_tip15: lang.wx_tip14}}</span>
                            </div>
                            <div class="fillter-list" v-if="isShowFilrer && fillterDomainSuffix.length > 0">
                              <div class="fillter-item" :class="{'is_select':selectFilterSuffix.includes(item)}"
                                v-for="item in fillterDomainSuffix" :key="item" @click="handelFilterSuffix(item)">
                                {{item}}
                              </div>
                            </div>
                            <div class="domain-list">
                              <div class="domain-item" v-for="(item,index) in calcDomainList" :key="index">
                                <div class="item-left">
                                  <span class="domain-name">{{item.name}}</span>
                                  <span class="domain-status" v-if="item.avail === 0">{{lang.template_text97}}</span>
                                  <span class="domain-status"
                                    v-if="(item.avail === 1 || item.avail === -2) && item.description">{{item.description}}</span>
                                </div>
                                <div class="item-right">
                                  <div class="premium-type" v-if="item.type && item.type === 'premium'">
                                    {{lang.template_text98}}
                                  </div>
                                  <el-popover placement="bottom" trigger="hover" v-if="item.avail === 1">
                                    <div class="pirce-box" slot="reference" v-loading="item.priceLoading">
                                      <span class="now-price">{{commonData.currency_prefix}}<span
                                          style="font-size: 0.18rem;  color: var(--color-price-text);">{{item.showPrice}}
                                        </span><span style="color: #485169;">/{{lang.common_cloud_text112}}</span>
                                      </span>
                                      <i style="margin-left: 0.1rem;color: #F6F7FB;" class="el-icon-arrow-down"></i>
                                    </div>
                                    <div class="price-list">
                                      <div class="price-item">
                                        <div class="price-year"></div>
                                        <div class="price-new">{{lang.template_text99}}</div>
                                        <div class="price-renew">{{lang.template_text100}}</div>
                                      </div>
                                      <div class="price-item" v-for="items in item.priceArr" :key="items.buyyear">
                                        <div class="price-year">{{items.buyyear}}{{lang.template_text101}}</div>
                                        <div class="price-new">{{commonData.currency_prefix}}{{items.buyprice}}</div>
                                        <div class="price-renew">{{commonData.currency_prefix}}{{items.renewprice}}
                                        </div>
                                      </div>
                                    </div>
                                  </el-popover>
                                  <el-button class="add-btn" type="primary" :plain="isAddCart(item)"
                                    :disabled="isAddCart(item)" v-if="item.avail === 1 "
                                    @click="addCart(item)">{{lang.template_text102}}
                                  </el-button>
                                  <div class="whois-box" v-if="item.avail === 0" @click="goWhois(item)">
                                    {{lang.template_text103}}
                                  </div>
                                  <div v-if="item.avail === -1">{{lang.template_text104}}</div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <template v-else>
                            <div class="search-title">{{lang.template_text93}}</div>
                            <div class="start-search" v-loading="isSearching">
                              <img src="/{$template_catalog}/template/{$themes}/img/goodsList/search_domain.png" alt="">
                              <p>{{lang.template_text105}}</p>
                            </div>
                          </template>
                        </div>
                        <div class="batch-box" v-else>
                          <div class="batch-main">
                            <template
                              v-if="availList.length !== 0 || unavailList.length !==0 || faillList.length !== 0">
                              <div class="search-title">{{lang.template_text113}}({{availList.length}})</div>
                              <div class="avail-list" v-loading="batchLoading">
                                <!-- 可注册域名 -->
                                <el-checkbox-group v-model="batchCheckGroup" @change="handleBatchChange">
                                  <div class="batch-item" v-for="(item,index) in availList" :key="index">
                                    <div class="item-left">
                                      <el-checkbox :label="item.name">
                                        <span class="domain-name">{{item.name}}</span>
                                      </el-checkbox>
                                      <span class="domain-status"
                                        v-if="item.avail === 0">{{lang.template_text114}}</span>
                                      <span class="domain-status"
                                        v-if="(item.avail === 1 || item.avail === -2) && item.description">{{item.description}}</span>
                                    </div>
                                    <div class="item-right">
                                      <div class="premium-type" v-if="item.type && item.type === 'premium'">
                                        {{lang.template_text115}}
                                      </div>
                                      <el-popover placement="bottom" trigger="hover" v-if="item.avail === 1">
                                        <div class="pirce-box" slot="reference" v-loading="item.priceLoading">
                                          <span class="now-price">{{commonData.currency_prefix}}<span
                                              style="font-size: 0.18rem;  color: var(--color-price-text);">{{item.showPrice}}
                                            </span><span style="color: #485169;">/{{lang.common_cloud_text112}}</span>
                                          </span>
                                          <i style="margin-left: 0.1rem;color: #F6F7FB;" class="el-icon-arrow-down"></i>
                                        </div>
                                        <div class="price-list">
                                          <div class="price-item">
                                            <div class="price-year"></div>
                                            <div class="price-new">{{lang.template_text99}}</div>
                                            <div class="price-renew">{{lang.template_text100}}</div>
                                          </div>
                                          <div class="price-item" v-for="items in item.priceArr" :key="items.buyyear">
                                            <div class="price-year">{{items.buyyear}}{{lang.template_text101}}</div>
                                            <div class="price-new">{{commonData.currency_prefix}}{{items.buyprice}}
                                            </div>
                                            <div class="price-renew">
                                              {{commonData.currency_prefix}}{{items.renewprice}}
                                            </div>
                                          </div>
                                        </div>
                                      </el-popover>
                                      <el-button class="add-btn" type="primary" :plain="isAddCart(item)"
                                        :disabled="isAddCart(item)" v-if="item.avail === 1 "
                                        @click="addCart(item)">{{lang.template_text102}}</el-button>
                                    </div>
                                  </div>
                                </el-checkbox-group>
                              </div>
                              <div class="all-check" v-if="availList.length > 0">
                                <el-checkbox :indeterminate="isBatchIndeterminate" v-model="isBatchAllCheck"
                                  @change="handleBatchCheckAllChange">{{lang.template_text116}}</el-checkbox>
                                <el-button @click="addAllCart" type="primary"
                                  :loading="addAllLoading">{{lang.template_text117}}
                                </el-button>
                              </div>
                              <el-collapse v-model="activeNames" v-loading="batchLoading">
                                <el-collapse-item name="1" style="margin-top: 0.6rem;" v-show="unavailList.length > 0">
                                  <template slot="title">
                                    <div class="unavail-title">
                                      <span>{{lang.template_text118}}({{unavailList.length}})</span>
                                      <span class="open-text"
                                        v-if="activeNames.includes('1')">{{lang.template_text119}}</span>
                                      <span class="open-text" v-else>{{lang.template_text120}}</span>
                                    </div>
                                  </template>
                                  <div class="unavail-list">
                                    <div class="unavail-item" v-for="(item,index) in unavailList" :key="index">
                                      <span class="unavail-name">{{item.name}}</span>
                                      <span class="unavail-reason">{{item.reason}}</span>
                                    </div>
                                  </div>
                                </el-collapse-item>
                                <el-collapse-item name="2" style="margin-top: 0.4rem;" v-show="faillList.length > 0">
                                  <template slot="title">
                                    <div class="unavail-title">
                                      <span>{{lang.template_text121}}({{faillList.length}})</span>
                                      <span class="open-text"
                                        v-if="activeNames.includes('2')">{{lang.template_text119}}</span>
                                      <span class="open-text" v-else>{{lang.template_text120}}</span>
                                    </div>
                                  </template>
                                  <div class="unavail-list">
                                    <div class="unavail-item" v-for="(item,index) in faillList" :key="index">
                                      <span class="unavail-name">{{item.name}}</span>
                                      <span class="unavail-reason">{{item.reason}}</span>
                                    </div>
                                  </div>
                                </el-collapse-item>
                              </el-collapse>
                            </template>
                            <template v-else>
                              <div class="search-title">{{lang.template_text94}}</div>
                              <div class="batch-search" v-loading="batchLoading">
                                <img src="/{$template_catalog}/template/{$themes}/img/goodsList/search_domain.png"
                                  alt="">
                                <p>{{lang.template_text122}}</p>
                              </div>
                            </template>
                          </div>
                        </div>
                      </div>
                      <div class="domain-right">
                        <div class="car-top">
                          <span>
                            {{lang.template_text123}}
                          </span>
                          <span class="clear-car" @click="deleteClearCart()">
                            <svg t="1750669572885" class="icon" viewBox="0 0 1024 1024" version="1.1"
                              xmlns="http://www.w3.org/2000/svg" p-id="9781" width="16" height="16">
                              <path
                                d="M895.398771 267.822944 671.950695 267.822944 671.950695 100.238145c0-46.261745-37.501958-83.792903-83.790889-83.792903l-167.583792 0c-46.287924 0-83.79391 37.531157-83.79391 83.792903l0 167.584799-223.445056 0c-61.698102 0-111.723535 50.024426-111.723535 111.723535l0 55.860257c0 41.398553 22.522722 77.540227 55.981082 96.841965 0.190299 56.371748-0.120825 280.654522-0.120825 377.979786 3.818059 112.787799 111.723535 111.723535 111.723535 111.723535l307.238966 0 0-0.872958 20.968111 0c2.225187 0.568883 4.557103 0.872958 6.961514 0.872958s4.736326-0.304075 6.961514-0.872958l181.59341 0c2.225187 0.568883 4.557103 0.872958 6.961514 0.872958s4.735319-0.304075 6.960507-0.872958l132.69366 0c0 0 107.902455 1.066278 111.721521-111.532229 0-97.144027-0.310117-320.985791-0.119818-377.298133 33.45836-19.301738 55.983096-55.443412 55.983096-96.842972L1007.1213 379.546479C1007.122306 317.84737 957.096873 267.822944 895.398771 267.822944zM811.604862 965.328556l-83.790889 0L727.813973 742.644695c0-15.43837-12.494276-27.929625-27.930632-27.929625-15.43837 0-27.932646 12.491255-27.932646 27.929625l0 222.683861-139.65316 0L532.297535 742.644695c0-15.43837-12.491255-27.929625-27.930632-27.929625-15.43837 0-27.929625 12.491255-27.929625 27.929625l0 223.44707c0 0-69.151976 0-139.654167 0L336.783111 742.644695c0-15.43837-12.491255-27.929625-27.930632-27.929625-15.43837 0-27.929625 12.491255-27.929625 27.929625l0 223.44707-83.79391 0c-47.459923 0-84.064758-31.259351-83.790889-83.792903 0.351398-71.180823 0.136935-263.033253 0.040275-335.168591l781.981174 0c-0.097667 72.050761-0.311123 263.506483 0.040275 334.540303C895.670627 934.122569 859.066799 965.328556 811.604862 965.328556zM951.260036 435.406736c0 30.849554-25.01171 55.860257-55.860257 55.860257L113.337047 491.266993c-30.849554 0-55.862271-25.01171-55.862271-55.860257L57.474777 379.546479c0-30.849554 25.013724-55.863278 55.862271-55.863278l279.308334 0 0-167.583792c0-46.259732 37.502965-83.792903 83.790889-83.792903l55.860257 0c46.287924 0 83.792903 37.533171 83.792903 83.792903l0 167.583792 279.308334 0c30.849554 0 55.860257 25.013724 55.860257 55.863278L951.258022 435.406736z"
                                p-id="9782"></path>
                            </svg>
                            {{lang.template_text124}}</span>
                        </div>
                        <div class="car-box" v-loading="isCarLoading">
                          <div class="car-no" v-if="carList.length === 0">
                            {{lang.template_text125}}
                            <span v-show="carList.length === 0">{{lang.template_text126}}</span>
                            <span v-if="!isLogin" class="blue-a-text" @click="goLogin"> {{lang.template_text127}}</span>
                          </div>
                          <div class="car-list" v-else>
                            <el-checkbox-group v-model="checkList" @change="handleCheckedCitiesChange">
                              <div class="car-item" v-for="(item,index) in carList" :key="index">
                                <div class="caritem-top">
                                  <div class="car-name">
                                    <el-checkbox :label="item.positions">
                                      <span class="shop-name">{{item.config_options.domain}}</span>
                                    </el-checkbox>
                                    <div class="car-del" @click="deleteCart(item)">
                                      <svg t="1750669572885" class="icon" viewBox="0 0 1024 1024" version="1.1"
                                        xmlns="http://www.w3.org/2000/svg" p-id="9781" width="16" height="16">
                                        <path
                                          d="M895.398771 267.822944 671.950695 267.822944 671.950695 100.238145c0-46.261745-37.501958-83.792903-83.790889-83.792903l-167.583792 0c-46.287924 0-83.79391 37.531157-83.79391 83.792903l0 167.584799-223.445056 0c-61.698102 0-111.723535 50.024426-111.723535 111.723535l0 55.860257c0 41.398553 22.522722 77.540227 55.981082 96.841965 0.190299 56.371748-0.120825 280.654522-0.120825 377.979786 3.818059 112.787799 111.723535 111.723535 111.723535 111.723535l307.238966 0 0-0.872958 20.968111 0c2.225187 0.568883 4.557103 0.872958 6.961514 0.872958s4.736326-0.304075 6.961514-0.872958l181.59341 0c2.225187 0.568883 4.557103 0.872958 6.961514 0.872958s4.735319-0.304075 6.960507-0.872958l132.69366 0c0 0 107.902455 1.066278 111.721521-111.532229 0-97.144027-0.310117-320.985791-0.119818-377.298133 33.45836-19.301738 55.983096-55.443412 55.983096-96.842972L1007.1213 379.546479C1007.122306 317.84737 957.096873 267.822944 895.398771 267.822944zM811.604862 965.328556l-83.790889 0L727.813973 742.644695c0-15.43837-12.494276-27.929625-27.930632-27.929625-15.43837 0-27.932646 12.491255-27.932646 27.929625l0 222.683861-139.65316 0L532.297535 742.644695c0-15.43837-12.491255-27.929625-27.930632-27.929625-15.43837 0-27.929625 12.491255-27.929625 27.929625l0 223.44707c0 0-69.151976 0-139.654167 0L336.783111 742.644695c0-15.43837-12.491255-27.929625-27.930632-27.929625-15.43837 0-27.929625 12.491255-27.929625 27.929625l0 223.44707-83.79391 0c-47.459923 0-84.064758-31.259351-83.790889-83.792903 0.351398-71.180823 0.136935-263.033253 0.040275-335.168591l781.981174 0c-0.097667 72.050761-0.311123 263.506483 0.040275 334.540303C895.670627 934.122569 859.066799 965.328556 811.604862 965.328556zM951.260036 435.406736c0 30.849554-25.01171 55.860257-55.860257 55.860257L113.337047 491.266993c-30.849554 0-55.862271-25.01171-55.862271-55.860257L57.474777 379.546479c0-30.849554 25.013724-55.863278 55.862271-55.863278l279.308334 0 0-167.583792c0-46.259732 37.502965-83.792903 83.790889-83.792903l55.860257 0c46.287924 0 83.792903 37.533171 83.792903 83.792903l0 167.583792 279.308334 0c30.849554 0 55.860257 25.013724 55.860257 55.863278L951.258022 435.406736z"
                                          p-id="9782"></path>
                                      </svg>
                                      {{lang.template_text128}}
                                    </div>
                                  </div>
                                  <div class="car-year">
                                    <el-select v-model="item.selectYear" @change="(val)=>changeCart(val,item)">
                                      <el-option v-for="items in item.priceArr" :key="items.buyyear"
                                        :label="items.buyyear + lang.template_text101" :value="items.buyyear">
                                      </el-option>
                                    </el-select>
                                  </div>
                                </div>
                                <div class="car-bottom">
                                  <span>{{lang.template_text87}}：</span>
                                  <div v-loading="item.priceLoading" class="car-price">
                                    {{commonData.currency_prefix}}<span
                                      style="font-size: 0.18rem; margin-right: 0.02rem;">{{priceCalc(item)}} </span>
                                  </div>
                                </div>
                              </div>
                            </el-checkbox-group>
                          </div>
                        </div>
                        <div class="car-money">
                          <el-checkbox :indeterminate="isIndeterminate" v-model="isAllCheck"
                            @change="handleCheckAllChange">{{lang.template_text129}}</el-checkbox>
                          <div class="mon-right">
                            <p class="now-price">
                              {{lang.template_text87}}:
                              <span class="money-text">{{commonData.currency_prefix}}<span
                                  style="font-size: 0.24rem;">{{totalMoneyCalc.toFixed(2)}}</span>
                              </span>
                            </p>
                            <p class="original-price" v-if="showOriginal">
                              <span class="hide">{{lang.template_text87}}:{{commonData.currency_prefix}}</span>
                              {{originalPrice}}
                            </p>
                          </div>
                        </div>
                        <div class="car-settle">
                          <el-button type="primary" class="settle-btn"
                            @click="goBuyDomain">{{lang.template_text130}}</el-button>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
              <p v-if="!isDomain && !scrollDisabled && goodsList.length !==0" class="tips">{{lang.goods_loading}}</p>
              <!-- <p v-if="!isDomain && scrollDisabled && goodsList.length !== 0" class="tips">{{lang.no_more_goods}}</p> -->
            </div>
          </div>
        </el-main>
        <div class="up-dialog">
          <el-dialog width="6.8rem" :visible.sync="isShowUpload" :show-close=false>
            <div class="dia-title">{{lang.template_text131}}</div>
            <div class="dia-concent">
              <p class="up-tips">{{lang.template_text132}}</p>
              <div class="file-box">
                <input accept="text/plain" type="file" id="upFile" autocomplete="off" tabindex="-1"
                  style="display: none;">
                <input class="file-name" :placeholder="lang.template_text133" readonly :value="fileName">
                <!-- 选择文件按钮 -->
                <div class="file-btn" @click="selectFile">{{lang.template_text134}}</div>
              </div>
            </div>
            <div class="dia-foter">
              <el-button class="confim-btn" @click="confirmUpload">{{lang.template_text135}}</el-button>
              <el-button class="cancel-btn" @click="cancelUpload">{{lang.template_text136}}</el-button>
            </div>
          </el-dialog>
        </div>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/components/scrollText/scrollText.js"></script>
  <script src="/{$template_catalog_cart}/template/{$themes_cart}/api/goodsList.js"></script>
  <script src="/{$template_catalog_cart}/template/{$themes_cart}/js/goodsList.js"></script>
  <script src="/{$template_catalog_cart}/template/{$themes_cart}/api/product.js"></script>

<script src="/{$template_catalog}/template/{$themes}/js/common/jquery.mini.js"></script>
<link rel="stylesheet" href="/{$template_catalog_cart}/template/{$themes_cart}/css/goods.css">
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
  <div class="goods">

    <el-container>
      <aside-menu></aside-menu>
      <el-container>
        <top-menu :num="shoppingCarNum"></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <!-- 后端渲染出来的配置页面 -->
          <div class="config-box">
            <el-popover placement="bottom-start" trigger="hover" popper-class="goods-change-box"
              v-if="commonData.cart_change_product == 1">
              <div class="goods-item-name" slot="reference">
                {{secProductGroupList[0]?.goodsList[0]?.product_group_name_first}}
                <i class="el-icon-arrow-down el-icon--right"></i>
              </div>
              <div class="goods-item-box">
                <el-input style="width:2rem; margin-bottom: .2rem;" v-model="fillterKey" suffix-icon="el-icon-search"
                  clearable :placeholder="lang.search_placeholder">
                </el-input>
                <div class="goods-group-item" v-for="item in calcProductGroup" :key="item.id">
                  <div class="goods-group-name">{{item.name}}</div>
                  <div class="goods-group-info">
                    <div class="option-name" v-for="option in item.goodsList" @click="handleCommand(option.id)">
                      {{option.name}}
                    </div>
                  </div>
                </div>
              </div>
            </el-popover>
            <div class="content"></div>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
  <script src="/{$template_catalog_cart}/template/{$themes_cart}/api/product.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/discountCode/discountCode.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/eventCode/eventCode.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/customGoods/customGoods.js"></script>
  <script src="/{$template_catalog_cart}/template/{$themes_cart}/js/goods.js"></script>

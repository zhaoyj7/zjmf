{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/productdetail.css">
</head>

<body>
  <!-- mounted之前显示 -->
  <div class="product_detail template">
    <el-container>
      <aside-menu></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <!-- 后端渲染出来的配置页面 -->
          <div class="config-box" v-if="timeouted">
            <!-- 电子合同判断 -->
            <div class="contract-box" v-if="actStatus.includes('unable_access')">
              <div class="contract-top">
                <svg t="1749023272025" class="back-img" viewBox="0 0 1024 1024" version="1.1" @click="goBack"
                  style="cursor: pointer;" xmlns="http://www.w3.org/2000/svg" p-id="20485" width="0.26rem"
                  height="0.26rem">
                  <path
                    d="M672.426667 209.92H455.68v-136.533333l-295.253333 170.666666 295.253333 170.666667v-136.533333h215.04C819.2 278.186667 938.666667 397.653333 938.666667 546.133333s-119.466667 267.946667-267.946667 267.946667H52.906667c-18.773333 0-34.133333 15.36-34.133334 34.133333s15.36 34.133333 34.133334 34.133334h619.52c186.026667 0 336.213333-150.186667 336.213333-336.213334s-151.893333-336.213333-336.213333-336.213333z"
                    p-id="20486" fill="var(--color-primary)"></path>
                </svg>
                <span class="top-product-name">{{hostData.product_name}}</span>
              </div>
              <div class="go-contract">
                <img class="contract-img" src="/{$template_catalog}/template/{$themes}/img/common/contract_img.png" />
                <div class="contract-text">{{lang.product_text1}}</div>
                <el-button class="contract-btn" @click="goContractDetail">{{lang.product_text2}}</el-button>
              </div>
            </div>
            <div class="content" v-else></div>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/js/common/jquery.mini.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/safeConfirm/safeConfirm.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/captchaDialog/captchaDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/countDownButton/countDownButton.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/hostStatus/hostStatus.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/discountCode/discountCode.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/cashBack/cashBack.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/flowPacket/flowPacket.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/batchRenewpage/batchRenewpage.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/autoRenew/autoRenew.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/ipDefase/ipDefase.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/resetAuth/resetAuth.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/components/renewDialog/renewDialog.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
  <script src="/{$template_catalog}/template/{$themes}/js/productdetail.js"></script>

  {include file="footer"}

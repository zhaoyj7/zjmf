{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/NotFound.css" />

</head>

<body>
  <div class="template">
    <el-container>
      <aside-menu></aside-menu>
      <el-container>
        <top-menu></top-menu>
        <el-main>
          <!-- 自己的东西 -->
          <div class="main-card">
            <div class="content-box">
              <div class="img-box">
                <img src="/{$template_catalog}/template/{$themes}/img/common/404.png" alt="">
              </div>
              <div class="tips-box">
                {{lang.status_text1}}
                <p class="tran-again" @click="goBack">{{lang.status_text2}}</p>
              </div>
            </div>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
  <!-- =======页面独有======= -->
  <script src="/{$template_catalog}/template/{$themes}/js/NotFound.js"></script>
  {include file="footer"}

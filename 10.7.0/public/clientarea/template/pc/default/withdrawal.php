{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/withdrawal.css">
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
                <el-main>
                    <!-- 自己的东西 -->
                    <div class="main-card">
                        <header>
                            <svg t="1749023272025" class="back-icon" viewBox="0 0 1024 1024" version="1.1" @click="back"
                                xmlns="http://www.w3.org/2000/svg" p-id="20485" width="0.26rem" height="0.26rem">
                                <path
                                    d="M672.426667 209.92H455.68v-136.533333l-295.253333 170.666666 295.253333 170.666667v-136.533333h215.04C819.2 278.186667 938.666667 397.653333 938.666667 546.133333s-119.466667 267.946667-267.946667 267.946667H52.906667c-18.773333 0-34.133333 15.36-34.133334 34.133333s15.36 34.133333 34.133334 34.133334h619.52c186.026667 0 336.213333-150.186667 336.213333-336.213334s-151.893333-336.213333-336.213333-336.213333z"
                                    p-id="20486" fill="var(--color-primary)"></path>
                            </svg>
                            <h2>{{lang.finance_btn9}}</h2>
                        </header>
                        <div class="withdrawal-content">
                            <el-table :data="withdrawalArr">
                                <el-table-column prop="withdraw_amount" :label="lang.finance_label17" width="150">
                                    <template slot-scope="{row}">
                                        <span>￥{{row.withdraw_amount}}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="create_time" :label="lang.security_label4" align="center"
                                    min-width="300">
                                    <template slot-scope="{row}">
                                        {{ row.create_time | formateTime}}
                                    </template>
                                </el-table-column>
                                <el-table-column prop="state" :label="lang.finance_label4" width="150">
                                    <template slot-scope="{row}">
                                        <el-tag :type="row.stateName" v-if="!row.reason">{{ row.stateText}}</el-tag>
                                        <el-tooltip :content="row.reason" placement="top" v-else>
                                            <el-tag :type="row.stateName">{{ row.stateText}}</el-tag>
                                        </el-tooltip>
                                    </template>
                                </el-table-column>

                            </el-table>
                            <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange">
                            </pagination>
                        </div>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/js/withdraw.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/api/withdraw.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    {include file="footer"}

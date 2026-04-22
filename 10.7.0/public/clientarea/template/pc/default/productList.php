{include file="header"}
<!-- 页面独有样式 -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/productList.css" />
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
    <div class="product">
        <el-container>
            <aside-menu></aside-menu>
            <el-container>
                <top-menu></top-menu>
                <el-main>
                    <div class="main-card">
                        <div class="main-card-title">
                            <span class="title-text">{{lang.product_status9}}</span>
                        </div>
                        <!-- 表格 -->
                        <div class="main-card-table">
                            <product-filter :tab.sync="params.tab" @change="inputChange"
                                :count="countData"></product-filter>
                            <!-- 筛选 -->
                            <div class="main-card-search">
                                <batch-renewpage :tab="params.tab" :ids="multipleSelection" module-type="all"
                                    @success="inputChange" ref="batchRenewRef"></batch-renewpage>
                                <div class="right-search">
                                    <!-- 产品状态 -->
                                    <el-select v-model="params.status" @change="statusSelectChange"
                                        :placeholder="lang.com_config.select_pro_status" clearable>
                                        <el-option v-for="item in statusSelect" :key="item.id" :value="item.status"
                                            :label="item.label">
                                        </el-option>
                                    </el-select>
                                    <el-input v-model="params.keywords" :placeholder="lang.cloud_tip_2" clearable
                                        @clear="clearKey" @keyup.enter.native="inputChange">
                                    </el-input>
                                    <div class="search-btn" @Click="inputChange">{{lang.search}}</div>
                                </div>
                            </div>
                            <div class="table">
                                <el-table v-loading="loading" :data="cloudData"
                                    style="width: 100%; margin-bottom: 0.2rem" row-class-name="border-r-none"
                                    header-row-class-name="border-r-hover" border class="list-table-border"
                                    @sort-change="sortChange" @selection-change="handleSelectionChange">
                                    <el-table-column type="selection" width="60" :show-overflow-tooltip="true">
                                    </el-table-column>
                                    <el-table-column prop="id" label="ID" width="100" align="left">
                                        <template slot-scope="scope">
                                            <span class="column-id" @click="toDetail(scope.row)">{{scope.row.id}}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="product-name" :label="lang.cart_tip_text4"
                                        :show-overflow-tooltip="true">
                                        &nbsp;
                                        <template slot-scope="scope">
                                            <p class="cloud-name" @click="toDetail(scope.row)">
                                                <span class="packge-name">{{ scope.row.product_name }}</span>
                                                <span class="name">{{ scope.row.name }}</span>
                                            </p>
                                        </template>
                                    </el-table-column>

                                    <el-table-column label="IP" :show-overflow-tooltip="true" class-name="list-show-ip">
                                        <template slot-scope="scope">
                                            <template v-if="scope.row.ip && scope.row.status !== 'Deleted'">
                                                <div class="com-ip-box">
                                                    <span>{{scope.row.ip}}</span>
                                                    <el-popover placement="top" trigger="hover"
                                                        v-if="scope.row.ip_num > 1">
                                                        <div class="ips">
                                                            <p v-for="(item,index) in scope.row.allIp" :key="index">
                                                                {{item}}
                                                                <i class="el-icon-document-copy base-color"
                                                                    @click="copyIp(item)"></i>
                                                            </p>
                                                        </div>
                                                        <span slot="reference" class="base-color">
                                                            ({{scope.row.ip_num}}) </span>
                                                    </el-popover>
                                                </div>
                                                <i class="el-icon-document-copy base-color"
                                                    @click="copyIp(scope.row.ip)"></i>
                                            </template>
                                            <template v-else>--</template>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="create_time" :label="lang.com_config.active_time" sortable>
                                        <template slot-scope="scope">
                                            <span>{{scope.row.create_time | formateTime}}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="due_time" :label="lang.index_text13" sortable>
                                        <template slot-scope="scope">
                                            <span>{{scope.row.due_time | formateTime}}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column :label="lang.finance_label4">
                                        <template slot-scope="scope">
                                            <div class="status"
                                                :style="'color:'+status[scope.row.status].color + ';background:' + status[scope.row.status].bgColor">
                                                {{status[scope.row.status].text }}
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column :label="lang.invoice_text139" :show-overflow-tooltip="true">
                                        <template slot-scope="{row}">
                                            <span>{{row.client_notes || '--'}}</span>
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </div>
                            <div class="page">
                                <pagination :page-data="params" @sizechange="sizeChange" @currentchange="currentChange">
                                </pagination>
                            </div>
                        </div>
                    </div>
                </el-main>
            </el-container>
        </el-container>
    </div>
    <!-- =======页面独有======= -->
    <script src="/{$template_catalog}/template/{$themes}/api/product.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/safeConfirm/safeConfirm.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/batchRenewpage/batchRenewpage.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/productFilter/productFilter.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/productList.js"></script>
    {include file="footer"}

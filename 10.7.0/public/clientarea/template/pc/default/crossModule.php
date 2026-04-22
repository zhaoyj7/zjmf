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
                        <!-- 表格 -->
                        <div class="main-card-table">
                            <product-filter :tab.sync="params.tab" @change="inputChange"
                                :count="countData"></product-filter>
                            <!-- 筛选 -->
                            <div class="main-card-search">
                                <batch-renewpage :tab="params.tab" :ids="multipleSelection" module-type="all"
                                    @success="inputChange" ref="batchRenewRef"></batch-renewpage>
                                <div class="right-search">
                                    <el-select v-model="params.index" @change="centerSelectChange" :filterable="true"
                                        :clearable="true" :placeholder="lang.data_center_filter"
                                        v-if="hasSelectField('area') && center.length > 0">
                                        <el-option v-for="(item,index) in center" :key="index" :value="index"
                                            :label="item.label">
                                            <div class="center-option-label">
                                                <img :src="'/upload/common/country/' + item.country_code + '.png'"
                                                    class="area-img" />
                                                <span class="option-text">{{item.label}}</span>
                                            </div>
                                        </el-option>
                                    </el-select>
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
                                    <el-table-column prop="id" label="ID" min-width="100" align="left">
                                        <template slot-scope="scope">
                                            <span class="column-id" @click="toDetail(scope.row)">{{scope.row.id}}</span>
                                        </template>
                                    </el-table-column>
                                    <!-- 区域 -->
                                    <el-table-column :label="lang.com_config.area" min-width="200"
                                        v-if="hasSelectField('area') && center.length > 0"
                                        :show-overflow-tooltip="true">
                                        <template slot-scope="scope">
                                            <div class="area" v-if="scope.row.country">
                                                <img :src="'/upload/common/country/' + scope.row.country_code + '.png'"
                                                    class="area-img" />
                                                <span class="area-country">{{scope.row.country}}</span>
                                                <span>-{{scope.row.city}}-{{scope.row.area}}</span>
                                            </div>
                                            <div v-else>--</div>
                                        </template>
                                    </el-table-column>
                                    <!-- 产品名称 -->
                                    <el-table-column prop="product-name" :label="lang.cart_tip_text4" min-width="170"
                                        :show-overflow-tooltip="true" v-if="hasSelectField('product_name')">
                                        &nbsp;
                                        <template slot-scope="scope">
                                            <p class="cloud-name" @click="toDetail(scope.row)">
                                                <span class="packge-name">{{ scope.row.product_name }}</span>
                                                <span class="name">{{ scope.row.name }}</span>
                                            </p>
                                        </template>
                                    </el-table-column>
                                    <!-- 计费方式 -->
                                    <el-table-column prop="billing_cycle" v-if="hasSelectField('billing_cycle')"
                                        :label="lang.billing_cycle" width="130" :show-overflow-tooltip="true">
                                        <template slot-scope="{row}">
                                            <span
                                                v-if="row.billing_cycle === 'on_demand' || row.billing_cycle === 'recurring_prepayment_on_demand'">
                                                {{lang.demand_fee}}
                                            </span>
                                            <span v-else>{{lang.month_year}}</span>
                                        </template>
                                    </el-table-column>
                                    <!-- 自动续费 -->
                                    <el-table-column prop="renew" width="120" :label="lang.auto_renew"
                                        v-if="hasAutoRenew && hasSelectField('is_auto_renew')">
                                        <template slot-scope="{row}">
                                            <auto-renew v-if="row.status === 'Active'" :id="row.id"
                                                :is-auto-renew="row.is_auto_renew" @update="getCloudList">
                                            </auto-renew>
                                            <span v-else>--</span>
                                        </template>
                                    </el-table-column>
                                    <!-- 基础信息 -->
                                    <el-table-column prop="base_info" :label="lang.base_info" min-width="130"
                                        v-if="hasSelectField('base_info')" :show-overflow-tooltip="true">
                                        <template slot-scope="scope">
                                            {{(scope.row.show_base_info === 1 && scope.row.base_info) || '--'}}
                                        </template>
                                    </el-table-column>

                                    <!-- <el-table-column :label="item.field_name" min-width="150"
                                        :show-overflow-tooltip="true" v-for="item in self_defined_field"
                                        :key="item.id + 'fff'">
                                        <template slot-scope="{row}">
                                            <span
                                                :class="item.field_type === 'textarea' ? 'word-pre' : ''">{{row.self_defined_field[item.id]
                                        ||'--'}}</span>
                                        </template>
                                    </el-table-column> -->

                                    <!-- IP -->
                                    <el-table-column label="IP" width="180" :show-overflow-tooltip="true"
                                        v-if="hasSelectField('ip')" class-name="list-show-ip">
                                        <template slot-scope="scope">
                                            <template v-if="scope.row.dedicate_ip && scope.row.status !== 'Deleted'">
                                                <div class="com-ip-box">
                                                    <span @click="toDetail(scope.row)">{{scope.row.dedicate_ip}}</span>
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
                                                    @click="copyIp(scope.row.allIp)" v-if="scope.row.ip_num > 0"></i>
                                            </template>
                                            <template v-else>--</template>
                                        </template>
                                    </el-table-column>

                                    <!-- OS -->
                                    <el-table-column label="OS" width="80" :show-overflow-tooltip="true"
                                        v-if="hasSelectField('os')">
                                        <template slot-scope="scope">
                                            <div class="os">
                                                <img :title="scope.row.image_name" v-if="scope.row.image_icon"
                                                    class="os-img"
                                                    :src="'/plugins/server/mf_cloud/template/clientarea/pc/default/img/mf_cloud/'+scope.row.image_icon +'.svg'" />
                                                <span v-else>--</span>
                                            </div>
                                        </template>
                                    </el-table-column>

                                    <!-- 创建时间 -->
                                    <el-table-column prop="active_time" :label="lang.com_config.active_time" width="160"
                                        v-if="hasSelectField('active_time')" sortable>
                                        <template slot-scope="scope">
                                            <span>{{scope.row.active_time | formateTime}}</span>
                                        </template>
                                    </el-table-column>

                                    <!-- 到期时间 -->
                                    <el-table-column prop="due_time" v-if="hasSelectField('due_time')" width="160"
                                        :label="lang.index_text13" sortable>
                                        <template slot-scope="scope">
                                            <span>{{scope.row.due_time | formateTime}}</span>
                                        </template>
                                    </el-table-column>

                                    <!-- 产品状态 -->
                                    <el-table-column :label="lang.finance_label4" v-if="hasSelectField('status')"
                                        width="100">
                                        <template slot-scope="scope">
                                            <div class="status"
                                                :style="'color:'+status[scope.row.status].color + ';background:' + status[scope.row.status].bgColor">
                                                {{status[scope.row.status].text }}
                                            </div>
                                        </template>
                                    </el-table-column>

                                    <!-- 备注 -->
                                    <el-table-column :label="lang.invoice_text139" min-width="100"
                                        :show-overflow-tooltip="true" v-if="hasSelectField('notes')">
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
    <script src="/{$template_catalog}/template/{$themes}/components/autoRenew/autoRenew.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/payDialog/payDialog.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/pagination/pagination.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/safeConfirm/safeConfirm.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/batchRenewpage/batchRenewpage.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/components/productFilter/productFilter.js"></script>
    <script src="/{$template_catalog}/template/{$themes}/js/crossModule.js"></script>
    {include file="footer"}

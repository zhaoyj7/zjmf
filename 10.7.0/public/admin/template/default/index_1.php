{include file="header"}
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/index.css">
<!-- =======内容区域======= -->
<div id="content" class="home-drag" v-cloak>
  <com-config>
    <div class="template-container">
      <div class="top-info">
        <div class="top-left">
          <div class="logo-box">
            <img src="/{$template_catalog}/template/{$themes}/img/index/logo.svg" alt="logo">
          </div>
          <div class="hello-text">
            下午好，欢迎您的使用！
          </div>
        </div>
        <div class="top-right">
          <div class="active-user">
            <span class="active-user-text">活跃用户(人)</span>
            <span class="divider"></span>
            <span class="active-user-num">324</span>
          </div>
          <div class="active-user">
            <span class="active-user-text">即将到期机柜数量(台)</span>
            <span class="divider"></span>
            <span class="active-user-num">16</span>
            <span class="active-op-text">去处理</span>
          </div>
          <div class="setting-box">
            <img src="/{$template_catalog}/template/{$themes}/img/index/setting.svg" class="setting-icon" alt="setting">
          </div>
        </div>
      </div>
      <div class="custom-box">
        <t-row :gutter="[24]" id="drag-row">
          <draggable v-model="showList" chosen-class="chosen" handle=".mover" force-fallback="true" animation="300"
            @start="onStart" @end="onEnd">
            <transition-group>
              <t-col :sm="item.w" :md="item.w" :xl="item.w" v-for="(item,index) in showList" :key="item.i">
                <div class="custom-item profit-box" v-if="item.i === 1">
                  <div class="profit-title">
                    <div class="profit-title-left">
                      <span>净利润总收入</span>
                      <img class="profit-eyes" src="/{$template_catalog}/template/{$themes}/img/index/eyes.svg"
                        alt="eyes">
                    </div>
                    <div class="profit-title-right">
                      <t-select>
                        <t-option value="1" label="销售收入"></t-option>
                        <t-option value="2" label="成本支出"></t-option>
                      </t-select>
                    </div>
                  </div>
                  <div class="profit-money">
                    <div class="profit-money-top">
                      <span class="profit-money-pre">￥</span>
                      <span class="profit-money-num">721,992.34</span>
                    </div>
                    <div class="profit-money-bottom">
                      耶！今日您的收入又增加了一笔，￥235.12 ！
                    </div>
                  </div>
                  <div class="profit-detail">
                    <div class="profit-detail-item">
                      <div class="profit-detail-top">
                        <div class="profit-detail-dot"></div>
                        <div class="profit-detail-title">
                          今日销售额(元)
                        </div>
                      </div>
                      <div class="profit-detail-bottom">
                        <div class="profit-detail-num">￥1,992.34</div>
                        <div class="profit-detail-rate up">
                          <img src="/{$template_catalog}/template/{$themes}/img/index/rate-up.svg" alt="up">
                          <span>+ 19%</span>
                        </div>
                      </div>
                    </div>
                    <div class="profit-detail-item">
                      <div class="profit-detail-top">
                        <div class="profit-detail-dot"></div>
                        <div class="profit-detail-title">
                          本日销售额(元)
                        </div>
                      </div>
                      <div class="profit-detail-bottom">
                        <div class="profit-detail-num">￥51,992.34</div>
                        <div class="profit-detail-rate down">
                          <img src="/{$template_catalog}/template/{$themes}/img/index/rate-down.svg" alt="up">
                          <span>- 9%</span>
                        </div>
                      </div>
                    </div>
                    <div class="profit-detail-item">
                      <div class="profit-detail-top">
                        <div class="profit-detail-dot"></div>
                        <div class="profit-detail-title">
                          今年销售额(元)
                        </div>
                      </div>
                      <div class="profit-detail-bottom">
                        <div class="profit-detail-num">￥831,992.34</div>
                        <div class="profit-detail-rate up">
                          <img src="/{$template_catalog}/template/{$themes}/img/index/rate-up.svg" alt="up">
                          <span>+ 19%</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="custom-item big-box" v-if="item.i === 2">
                  <div class="big-title">
                    本年大客户统计
                  </div>
                  <div class="big-list">
                    <div class="big-list-item" v-for="item in 4">
                      <div class="big-list-left">
                        <div class="big-list-index">
                          {{item}}
                        </div>
                        <div class="big-list-name">
                          大客户名称{{item}}
                        </div>
                      </div>
                      <div class="big-list-right">
                        <div class="big-item-pre">
                          ￥
                        </div>
                        <div class="big-item-money">
                          12,437,575.00
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="custom-item todo-box" v-if="item.i === 3">
                  <div class="todo-title">
                    待办事项
                  </div>
                  <div class="todo-list">
                    <div class="todo-list-item" v-for="item in 11">
                      <div class="todo-item-title">
                        待处理工单
                      </div>
                      <div class="todo-item-num">
                        <span class="todo-num-text">12</span><span class="todo-item-unit">条</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="custom-item user-box" v-if="item.i === 4">
                  <div class="user-title">
                    最近访问用户
                  </div>
                  <div class="user-list">
                    <div class="user-list-item" v-for="item in 4">
                      <div class="user-item-left">
                        <div class="user-item-index">{{item}}</div>
                        <div class="user-item-name">
                          用户名称{{item}}
                        </div>
                      </div>
                      <div class="user-item-right">
                        15分钟前
                      </div>
                    </div>
                  </div>
                </div>
                <div class="custom-item chart-box" v-if="item.i === 5">
                  <div class="chart-title">
                    <div class="chart-title-left">
                      <span>本年销售额</span>
                    </div>
                    <div class="chart-title-right">
                      <t-select>
                        <t-option value="1" label="本年销售详情"></t-option>
                        <t-option value="2" label="本年支出详情"></t-option>
                      </t-select>
                    </div>
                  </div>
                  <div class="chart-content" id="ThisYearSale"></div>
                </div>
                <t-icon name="move" class="mover"></t-icon>
              </t-col>
            </transition-group>
          </draggable>
        </t-row>
      </div>
    </div>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/js/common/jquery.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/common/echarts.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/common/Sortable.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/common/vuedraggable.umd.min.js"></script>
<script src="/{$template_catalog}/template/{$themes}/api/index.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/index.js"></script>
{include file="footer"}

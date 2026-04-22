{include file="header"}
<!-- =======内容区域======= -->
<link rel="stylesheet" href="/{$template_catalog}/template/{$themes}/css/system.css">
<div id="content" class="template feedback" v-cloak>
  <com-config>
    <t-card class="list-card-container">
      <ul class="common-tab">
        <li>
          <a href="template.htm">{{lang.feedback}}</a>
        </li>
        <li class="active">
          <a href="javascript:;">{{lang.guidance}}</a>
        </li>
      </ul>
      <div class="box">
        <t-table row-key="id" :data="list" size="medium" :columns="typeColumns" :hover="hover" :loading="loading" :table-layout="tableLayout ? 'auto' : 'fixed'" display-type="fixed-width" :hide-sort-tips="true">
          <template slot="sortIcon">
            <t-icon name="caret-down-small"></t-icon>
          </template>
          <template #username="{row}">
            {{row.username || '--'}}
          </template>
          <template #company="{row}">
            {{row.company || '--'}}
          </template>
          <template #phone="{row}">
            {{row.phone || '--'}}
          </template>
          <template #email="{row}">
            {{row.email || '--'}}
          </template>
        </t-table>
        <com-pagination v-if="total" :total="total"
          :page="params.page" :limit="params.limit"
          @page-change="changePage">
        </com-pagination>
      </div>
    </t-card>
  </com-config>
</div>
<!-- =======页面独有======= -->

<script src="/{$template_catalog}/template/{$themes}/api/system.js"></script>
<script src="/{$template_catalog}/template/{$themes}/js/consult.js"></script>
{include file="footer"}

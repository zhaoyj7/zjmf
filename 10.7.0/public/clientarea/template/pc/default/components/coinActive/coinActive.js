const coinActive = {
  template: /* html*/ `
  <el-popover placement="left-start" v-model="active_show" trigger="click" popper-class="coin-active-popover"
  @show="visbleShow" v-if="home_show_coin_activity == 1">
  <div class="coin-active-btn" slot="reference">
    <div class="coin-active-trigger" role="button" :aria-label="lang.coin_text71 + coin_name">
      <div class="trigger-icon" aria-hidden="true">
        <svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="none"
          version="1.1" width="16" height="16" viewBox="0 0 16 16">
          <defs>
            <clipPath id="master_svg0_783_42616">
              <rect x="0" y="0" width="16" height="16" rx="0" />
            </clipPath>
          </defs>
          <g clip-path="url(#master_svg0_783_42616)">
            <g>
              <path
                d="M13.6663418125,13.347250952929688L13.6663418125,6.6666717529296875L2.3330078125,6.6666717529296875L2.3330078125,13.347250952929688C2.3330078125,14.075946852929688,2.9237326425,14.666671752929688,3.6524287125,14.666671752929688L12.3469208125,14.666671752929688C13.0756168125,14.666671752929688,13.6663418125,14.075946852929688,13.6663418125,13.347250952929688"
                fill="var(--color-primary)" fill-opacity="1" style="mix-blend-mode:passthrough" />
              <path
                d="M2.4862143825,14.513465452929687Q2.9692757125,14.996526752929688,3.6524287125,14.996526752929688L12.3469208125,14.996526752929688Q13.0300728125,14.996526752929688,13.5131348125,14.513465452929687Q13.9961968125,14.030403652929689,13.9961968125,13.347250952929688L13.9961968125,6.6666717529296875Q13.9961968125,6.634183853929687,13.9898588125,6.602320201929688Q13.9835208125,6.570456549929688,13.9710878125,6.540441642929688Q13.9586548125,6.510426742929687,13.9406048125,6.483414042929687Q13.9225558125,6.456401352929688,13.8995838125,6.4334289129296875Q13.8766118125,6.410456512929687,13.8495988125,6.392407182929688Q13.8225858125,6.374357882929687,13.7925708125,6.361925302929688Q13.7625558125,6.349492732929687,13.7306918125,6.3431546429296874Q13.6988298125,6.336816552929688,13.6663418125,6.336816552929688L2.3330078125,6.336816552929688Q2.3005199135,6.336816552929688,2.2686562615,6.3431546429296874Q2.2367926095,6.349492732929687,2.2067777025,6.361925302929688Q2.1767628025,6.374357882929687,2.1497501025,6.392407182929688Q2.1227374125,6.410456512929687,2.0997649725,6.4334289129296875Q2.0767925725,6.456401352929688,2.0587432425,6.483414052929687Q2.0406939425,6.510426742929687,2.0282613625,6.540441642929688Q2.0158287925,6.570456549929688,2.0094907025,6.602320201929688Q2.0031526125,6.634183853929687,2.0031526125,6.6666717529296875L2.0031526125,13.347250952929688Q2.0031526125,14.030403652929689,2.4862143825,14.513465452929687ZM12.3469208125,14.336816752929687L3.6524287125,14.336816752929687Q3.2425374424999998,14.336816752929687,2.9527000825,14.046979452929687Q2.6628630125,13.757142552929688,2.6628630125,13.347250952929688L2.6628630125,6.996526952929687L13.3364868125,6.996526952929687L13.3364868125,13.347250952929688Q13.3364868125,13.757141552929689,13.0466488125,14.046979452929687Q12.7568108125,14.336816752929687,12.3469208125,14.336816752929687Z"
                fill-rule="evenodd" fill="var(--color-primary)" fill-opacity="1" style="mix-blend-mode:passthrough" />
            </g>
            <g>
              <path
                d="M8.65971041,6.6666717529296875L8.660000029999999,6.6666717529296875L8.66000301,6.006668742929688L7.33999699,6.006668742929688L7.33999997,6.6666717529296875L7.34028959,6.6666717529296875L7.34028959,14.333338752929688L7.33999997,14.333338752929688L7.33999699,14.993341452929688L8.66000301,14.993341452929688L8.660000029999999,14.333338752929688L8.65971041,14.333338752929688L8.65971041,6.6666717529296875Z"
                fill-rule="evenodd" fill="#FFFFFF" fill-opacity="1" style="mix-blend-mode:passthrough" />
            </g>
            <g>
              <rect x="1.3330078125" y="4" width="13.333333969116211" height="2.6666667461395264"
                rx="1.3194208145141602" fill="var(--color-primary)" fill-opacity="1"
                style="mix-blend-mode:passthrough" />
              <path
                d="M1.0031526125,5.3472459L1.0031526125,5.3194208Q1.0031526125,4.6362685,1.4862143825,4.15320657Q1.9692761325,3.6701448,2.6524286125,3.6701448L13.3469218125,3.6701448Q14.0300738125,3.6701448,14.5131358125,4.1532066499999996Q14.9961968125,4.6362685,14.9961968125,5.3194208L14.9961968125,5.3472459Q14.9961968125,6.0303984,14.5131358125,6.5134602Q14.0300738125,6.996521899999999,13.3469218125,6.996521899999999L2.6524286125,6.996521899999999Q1.9692763725,6.996521899999999,1.4862143825,6.5134602Q1.0031526125,6.0303984,1.0031526125,5.3472459ZM1.6628630125,5.3472459Q1.6628630725,5.7571373,1.9527000825,6.0469744Q2.2425371425,6.3368115,2.6524286125,6.3368115L13.3469218125,6.3368115Q13.7568118125,6.3368115,14.0466488125,6.0469744Q14.3364858125,5.7571373,14.3364858125,5.3472459L14.3364858125,5.3194208Q14.3364858125,4.90952921,14.0466488125,4.61969221Q13.7568118125,4.3298552,13.3469218125,4.3298552L2.6524286125,4.3298552Q2.2425370225,4.32985526,1.9527000825,4.61969227Q1.6628630125,4.90952933,1.6628630125,5.3194208L1.6628630125,5.3472459Z"
                fill="var(--color-primary)" fill-opacity="1" style="mix-blend-mode:passthrough" />
            </g>
            <g>
              <path
                d="M4.8803459725,1.8136378270703126Q4.7811028325,1.7201073770703126,4.7270553125,1.5949034670703126Q4.6730077825,1.4696996070703126,4.6730077825,1.3333282470703125Q4.6730078425,1.2683239280703125,4.6856895725,1.2045686570703125Q4.6983712925,1.1408133670703124,4.7232474125,1.0807571970703125Q4.7481235225,1.0207010470703124,4.7842379225,0.9666519170703125Q4.8203523725,0.9126027870703125,4.8663173625,0.8666377970703125Q4.9122823525,0.8206728070703125,4.9663314825,0.7845583570703125Q5.0203806125,0.7484439570703125,5.0804367625,0.7235678470703125Q5.1404929325,0.6986917270703125,5.2042482225,0.6860100070703125Q5.2680034935,0.6733282770703125,5.3330078125,0.6733282170703125Q5.4693791725,0.6733282170703125,5.5945830325,0.7273757470703125Q5.7197869425,0.7814232670703125,5.8133173925,0.8806664070703125L7.9996745125,3.0670235470703124L10.1860304125,0.8806677470703125Q10.2795610125,0.7814239870703125,10.4047651125,0.7273761070703125Q10.5299692125,0.6733282170703125,10.6663413125,0.6733282170703125Q10.7313456125,0.6733282770703125,10.7951012125,0.6860100070703125Q10.858856212500001,0.6986917270703125,10.9189119125,0.7235678470703125Q10.978967712500001,0.7484439570703125,11.0330171125,0.7845583570703125Q11.0870657125,0.8206728070703125,11.1330309125,0.8666377970703125Q11.1789961125,0.9126027870703125,11.2151103125,0.9666519170703125Q11.2512250125,1.0207010470703124,11.276101112500001,1.0807571970703125Q11.300977212500001,1.1408133670703124,11.3136592125,1.2045686570703125Q11.3263407125,1.2683239280703125,11.3263411125,1.3333282470703125Q11.3263416125,1.4696971770703124,11.272295912499999,1.5948992370703126Q11.2182503125,1.7201012670703126,11.1190100125,1.8136314470703125L8.4661603125,4.466480747070312Q8.4202154125,4.512425447070313,8.3661899125,4.548523947070313Q8.3121643125,4.5846223470703125,8.2521345125,4.609487547070312Q8.1921048125,4.634352647070313,8.1283774125,4.6470289470703126Q8.0646500125,4.659705147070312,7.9996743125,4.659705147070312Q7.9346986125,4.659705147070312,7.8709710125,4.6470289470703126Q7.807243812499999,4.634352647070313,7.7472138125,4.609487547070312Q7.687184112500001,4.5846223470703125,7.6331589125,4.548523947070313Q7.5791335125,4.512425447070313,7.533188812500001,4.466480747070312L4.8803459725,1.8136378270703126Z"
                fill-rule="evenodd" fill="var(--color-primary)" fill-opacity="1" style="mix-blend-mode:passthrough" />
            </g>
          </g>
        </svg>
      </div>
      <div class="trigger-text">{{lang.coin_text71}}{{coin_name}}</div>
    </div>
  </div>
  <div class="coin-active-box" v-loading="loading">
    <div class="coin-title">
      {{coin_name}}{{lang.coin_text72}}
    </div>
    <template v-if="product_id || host_id">
      <el-tabs v-model="activeTab" @tab-click="handleTabClick" class="coin-active-tabs">
        <el-tab-pane :label="lang.coin_text76" name="current">
        </el-tab-pane>
        <el-tab-pane :label="lang.coin_text77" name="all">
        </el-tab-pane>
      </el-tabs>
    </template>
    <div class="coin-list">
      <template v-if="coinActiveList.length === 0 && !loading">
        <el-empty :description="lang.order_text15"></el-empty>
      </template>
      <template v-else>
        <div class="coin-list-item" v-for="item in coinActiveList" :key="item.id">
          <div class="coin-item-icon">
            <svg class="item-icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
              fill="none" version="1.1" width="24" height="24" viewBox="0 0 24 24">
              <defs>
                <clipPath id="master_svg0_783_43860">
                  <rect x="0" y="0" width="24" height="24" rx="0" />
                </clipPath>
              </defs>
              <g clip-path="url(#master_svg0_783_43860)">
                <g>
                  <path
                    d="M18.708139875,3.64892128671875C16.746531875000002,-0.54723961328125,13.474185875,0.72000699871875,12.082462875,1.87477598671875C11.011678675,4.83818338671875,8.189241875,9.15282158671875,6.912997675,10.94046338671875C9.057568575000001,10.38557438671875,10.998181375,13.12552738671875,11.700040775,14.56673738671875C11.914497375,13.85588038671875,16.461587875,6.99325228671875,18.708139875,3.64892128671875ZM18.132253875,5.46505788671875C18.618157875,6.10842898671875,20.269326875,7.25570008671875,22.995780875,6.69931168671875C20.890203875,8.653420486718751,16.440591875000003,12.96355938671875,15.489781875,14.56823938671875C14.762426875,14.65822038671875,13.100760875,14.78419538671875,12.274424875,14.56823938671875C13.549170875,12.33068538671875,16.509577874999998,7.37867598671875,18.132253875,5.46505788671875ZM4.994881375,16.10993138671875C4.4579884750000005,16.31688838671875,3.546170875,17.24520338671875,4.189542275,19.31178838671875C4.432493975,20.01964738671875,5.246830675,21.54184538671875,6.565067275,21.97375638671875C5.7852234750000004,22.15371938671875,3.999080375,21.87327738671875,3.079764475,19.31178838671875C3.040772275,19.27279838671875,2.236932905,15.64802238671875,4.994881375,16.10993138671875ZM4.802920074999999,13.40747038671875L5.990683075,11.44136338671875C7.151450675,11.05444038671875,9.804419075,11.17891638671875,11.121155775,14.75869938671875C12.485883875,15.16961838671875,15.930694875,15.53854338671875,18.780124875,13.71640938671875C18.499681875,14.07633538671875,17.860808875,14.85768038671875,17.554869875,15.10513038671875C18.001779875,15.16961838671875,19.002080875,15.33608438671875,19.429494875,15.49055438671875C18.601659875,16.06943838671875,16.245629875,17.21970938671875,13.457687875,17.18821538671875C14.083063875,17.54814338671875,15.501779875,18.26050038671875,16.176644875,18.22900538671875C15.882704875,18.28149638671875,15.203340875,18.38347638671875,14.835913875,18.38347638671875C15.000878875,18.70591138671875,15.432794875,19.40927138671875,15.831713875,19.65822238671875C14.222536875,19.46326038671875,10.746232075,18.33848538671875,9.703939875,15.37507838671875C9.270526875,14.34628438671875,7.682343975,12.51365138671875,4.802920074999999,13.40747038671875ZM12.115457875,18.77339738671875C11.748030675,18.68041638671875,11.094161975,18.06703738671875,10.813717875,17.77009738671875C10.629254375,18.14052438671875,10.072865475,19.80219038671875,9.817916875,20.58653438671875C9.297520675000001,22.03674538671875,8.553669975,22.81059038671875,8.247730775,23.01605038671875L9.511978175,23.13302638671875C9.601959274999999,22.63812438671875,11.286123275,20.02115038671875,12.115457875,18.77339738671875ZM9.092061475000001,15.06763738671875C7.469387075,12.87807538671875,5.072865675,13.58893538671875,4.075565575000001,14.21880838671875L2.469386425,16.34088338671875C2.008978501,17.20471038671875,1.971486052,18.24400338671875,2.008978501,18.65492038671875C2.592361685,22.20471238671875,5.290322574999999,23.19451338671875,6.566566975,23.24550438671875C8.129253875,23.18401538671875,9.107058075000001,21.39337338671875,9.401000475,20.50705138671875C9.553969875,19.88917338671875,10.153849575,18.19301438671875,10.434293775,17.42066738671875C9.699440975,16.77279638671875,9.234533775,15.58203438671875,9.093561675,15.06763638671875L9.092061475000001,15.06763738671875ZM8.019775875,19.58173538671875C7.215936675,16.367880386718753,5.422295775,15.60003138671875,4.255528975,15.61203038671875C3.8776046749999997,15.61503038671875,3.510177975,15.76200138671875,3.232733275,16.01694938671875C2.706338465,16.50585138671875,2.527874235,17.12222838671875,2.506878435,17.42066938671875C2.401899485,19.74220438671875,3.448690075,21.19691238671875,4.496980475,21.99625238671875C5.303818975,22.61263038671875,6.490082275,23.00105238671875,7.280423675,22.36067938671875C8.070765975,21.72031038671875,8.136752575,20.38407538671875,8.019775875,19.58173538671875Z"
                    fill="var(--color-primary)" fill-opacity="1" style="mix-blend-mode:passthrough" />
                </g>
              </g>
            </svg>
          </div>
          <div class="coin-item-content">
            <div class="coin-item-content-top">
              <div class="coin-item-content-title-text">
                {{ item.name }}
              </div>
              <div class="coin-item-content-time">
                <template v-if="item.begin_time == 0 ">
                  {{lang.coin_text73}} - {{ item.end_time | formateTime }}
                </template>
                <template v-else>
                  {{ item.begin_time | formateTime }} - {{ item.end_time | formateTime }}
                </template>
              </div>
            </div>
            <div class="coin-item-content-desc">
              <span @click="item.show_desc = !item.show_desc">{{ lang.coin_text78 }}</span>
              <i class="el-icon-arrow-down" v-show="!item.show_desc" @click="item.show_desc = !item.show_desc"></i>
              <i class="el-icon-arrow-up" v-show="item.show_desc" @click="item.show_desc = !item.show_desc"></i>
            </div>
            <div class="coin-item-detail" v-if="item.show_desc">
              <div class="coin-detail-active-limit">
                {{lang.coin_text79}}
              </div>
              <div class="coin-active-detail">
                <template v-if="item.type === 'default'">
                  <!-- 公开送 -->
                  <p class="coin-detail-main-desc">
                    {{coin_name}}{{lang.coin_text80}}
                  </p>
                </template>
                <template v-if="item.type === 'property'">
                  <!-- 属性送 -->
                  <p class="coin-detail-main-desc">
                  <p>{{lang.coin_text81}}：{{item.property_type === 'register' ? lang.coin_text82 : lang.coin_text83}}
                  </p>
                  <p>{{lang.coin_text84}}：<template v-if="item.property_strategy === 'signal'">
                      {{lang.coin_text85}}
                    </template>
                    <template v-else>
                      {{lang.coin_text86}}{{item.property_cycle_num}}{{item.property_cycle_unit === 'day' ? lang.coin_text87 : lang.coin_text88}}，{{lang.coin_text89}}
                    </template>
                  </p>
                  <p>{{lang.coin_text90}}{{coin_name}}{{lang.coin_text91}}：
                    <template v-if="item.property_type === 'register'">
                      {{item.property_amount}}{{coin_name}}
                    </template>
                    <template v-else>
                      <p v-for="(property_item,index) in item.property_level" :key="index">
                        {{property_item.level_id_arr.map(item => item.name).join('、')}}，{{lang.coin_text92}}{{property_item.award}}{{coin_name}}
                      </p>
                    </template>
                  </p>
                  </p>
                </template>
                <template v-if="item.type === 'recharge'">
                  <!-- 充值送 -->
                  <p>{{lang.coin_text93}}：{{lang.coin_text94}}</p>
                  <p>{{lang.coin_text90}}{{coin_name}}{{lang.coin_text91}}：</p>
                  <template v-if="item.recharge_type === 'gradient'">
                    <p v-for="recharge_item in item.recharge_return" :key="recharge_item.amount">
                      {{lang.coin_text94}}{{recharge_item.amount}}，{{lang.coin_text92}}{{recharge_item.award}}{{coin_name}}
                    </p>
                  </template>
                  <template v-else>
                    <p>{{lang.coin_text94}}{{item.recharge_min}}{{lang.coin_text95}}，{{lang.coin_text96}}
                      {{item.recharge_proportion}}% {{lang.coin_text97}}{{coin_name}}
                    </p>
                  </template>
                </template>
                <template v-if="item.type === 'total_consume'">
                  <!-- 累计消费送 -->
                  <p>{{lang.coin_text81}}：{{lang.coin_text98}}</p>
                  <p v-if="item.total_consume_type === 'total'">{{lang.coin_text99}}：{{lang.coin_text100}}</p>
                  <p v-if="item.total_consume_type === 'stage'">{{lang.coin_text99}}：{{lang.coin_text101}}</p>
                  <p>{{lang.coin_text102}}：{{lang.coin_text103}}</p>
                  <p>{{lang.coin_text90}}{{coin_name}}{{lang.coin_text91}}：</p>
                  <template v-if="item.total_consume_type === 'total'">
                    <p v-for="total_consume_item in item.total_consume_total_return" :key="total_consume_item.amount">
                      {{lang.coin_text104}}{{total_consume_item.amount}}，{{lang.coin_text105}}{{total_consume_item.award}}{{coin_name}}
                    </p>
                  </template>
                  <template v-else>
                    <p v-for="total_consume_item in item.total_consume_stage_return" :key="total_consume_item.amount">
                      {{lang.coin_text106}}{{total_consume_item.amount}}，{{lang.coin_text105}}{{total_consume_item.award}}{{coin_name}}
                    </p>
                  </template>
                </template>
                <template v-if="item.type === 'scene'">
                  <!-- 场景送 -->
                  <p v-for="(action,index) in calculateSceneRange(item.rules)" :key="index">{{action}}</p>
                  <p>{{lang.coin_text102}}：
                  <span v-if="item.gift_strategy === 'rule_once'">{{lang.coin_text145}}</span>
                  <span v-if="item.gift_strategy === 'activity_once'">{{lang.coin_text146}}</span>
                  <span v-if="item.gift_strategy === 'multiple'">{{lang.coin_text147}}</span>
                  </p>
                </template>
                <template v-if="item.type === 'single_consume'">
                  <!-- 单笔消费送 -->
                  <p>{{lang.coin_text93}}：{{lang.coin_text98}}</p>
                  {{lang.coin_text107}}：<template v-if="item.single_consume_able_product_ids_arr?.length > 0">
                    <span v-for="(product_item,index) in item.single_consume_able_product_ids_arr"
                      :key="product_item.id">
                      <a :href="'/cart/goods.htm?id=' + product_item.id" target="_blank"
                        style="color: var(--color-primary);">{{product_item.name}}
                        <span v-if="index !== item.single_consume_able_product_ids_arr.length - 1">、</span>
                      </a>
                    </span>
                  </template>
                  <template v-else>
                    {{lang.coin_text108}}
                  </template>
                  </p>
                  <p>{{lang.coin_text99}}：{{lang.coin_text101}}</p>
                  <p>{{lang.coin_text102}}：{{lang.coin_text103}}</p>
                  <p>{{lang.coin_text90}}{{coin_name}}{{lang.coin_text91}}：</p>
                  <p v-for="single_consume_item in item.single_consume_return" :key="single_consume_item.amount">
                    {{lang.coin_text109}}{{single_consume_item.amount}}，{{lang.coin_text105}}{{single_consume_item.award}}{{coin_name}}
                  </p>
                </template>
                <template v-if="item.type === 'order'">
                  <!-- 订购送 -->
                  <p>{{lang.coin_text81}}：{{lang.coin_text110}}</p>
                  <p>{{lang.coin_text99}}：{{lang.coin_text111}}</p>
                  <p v-if="item.order_same_once === 1">{{lang.coin_text102}}：{{lang.coin_text112}}</p>
                  <p>{{lang.coin_text90}}{{coin_name}}{{lang.coin_text91}}：</p>
                  <p v-for="(order_item,index) in item.order_return" :key="index">
                    {{lang.coin_text110}}
                    <template v-if="order_item.product_ids_arr?.length > 0">
                      <span v-for="(product_item,indexs) in order_item.product_ids_arr" :key="product_item.id">
                        <a :href="'/cart/goods.htm?id=' + product_item.id" target="_blank"
                          style="color: var(--color-primary);">{{product_item.name}}</a>
                        <span v-if="indexs !== order_item.product_ids_arr.length - 1">、</span>
                      </span>
                    </template>
                    <template v-else>
                      {{lang.coin_text108}}
                    </template>
                    ，{{lang.coin_text105}}{{order_item.award}}{{coin_name}}
                  </p>
                </template>
                <template v-if="item.type === 'full_gift'">
                  <!-- 每满送 -->
                  <p>{{lang.coin_text93}}：
                    <template v-if="item.full_gift_send_scene_order == 1">
                      {{lang.coin_text110}}<template
                        v-if="item.full_gift_send_scene_renew == 1 || item.full_gift_send_scene_upgrade == 1">、</template>
                    </template>
                    <template v-if="item.full_gift_send_scene_renew == 1">
                      {{lang.coin_text113}}<template v-if="item.full_gift_send_scene_upgrade == 1">、</template>
                    </template>
                    <template v-if="item.full_gift_send_scene_upgrade == 1">
                      {{lang.coin_text114}}
                    </template>
                  </p>
                  <p>{{lang.coin_text115}}：
                    <template v-if="item.full_gift_client_limit_open == 0">
                      {{lang.coin_text116}}
                    </template>
                    <template v-else>
                      {{item.full_gift_client_limit_type === 'client' ? lang.coin_text117 : lang.coin_text118}}
                    </template>
                  </p>
                  <p>{{lang.coin_text107}}：<template v-if="item.full_gift_product_only_defence == 1">
                      ({{lang.coin_text119}})
                    </template>
                    <template v-if="item.full_gift_send_product_ids_arr?.length > 0">
                      <span v-for="(product_item,index) in item.full_gift_send_product_ids_arr" :key="product_item.id">
                        <a :href="'/cart/goods.htm?id=' + product_item.id" target="_blank"
                          style="color: var(--color-primary);">{{product_item.name}}
                          <span v-if="index !== item.full_gift_send_product_ids_arr.length - 1">、</span>
                        </a>
                      </span>
                    </template>
                    <template v-else>
                      {{lang.coin_text108}}
                    </template>

                  </p>
                  <p>
                    {{lang.coin_text102}}：{{lang.coin_text120}}{{item.full_gift_payment_threshold>0?item.full_gift_payment_threshold:item.full_gift_threshold_amount}}
                    <template v-if="item.full_gift_same_once == 1">，{{lang.coin_text121}}</template>
                  </p>
                  <p>{{lang.coin_text90}}{{coin_name}}{{lang.coin_text91}}：</p>
                  <p>
                    {{lang.coin_text122}}{{item.full_gift_threshold_amount}}，{{lang.coin_text105}}{{item.full_gift_gift_amount}}{{coin_name}}
                  </p>
                </template>
              </div>
              <div class="coin-detail-use-limit">{{coin_name}}{{lang.coin_text123}}</div>
              <p>{{lang.coin_text124}}：
                <template v-if="item.type == 'default'">
                  {{item.effective_start_time | formateTime}}
                </template>
                <template v-else-if="item.immediately_effective == 1 || item.effective_time == 0">
                  {{lang.coin_text125}}
                </template>
                <template v-else>
                  {{lang.coin_text126}}{{item.effective_time}}{{item.effective_time_unit === 'day' ? lang.coin_text87 : lang.coin_text88}}{{lang.coin_text127}}
                </template>
              </p>
              <p>{{lang.coin_text128}}：
                <template v-if="item.type == 'default'">
                  {{item.effective_end_time | formateTime}}
                </template>
                <template v-else-if="item.unlimit_efficient === 0">
                  {{item.efficient_time }} {{item.efficient_time_unit === 'day' ? lang.coin_text87 : lang.coin_text88}}
                </template>
                <template v-else>
                  {{lang.coin_text129}}
                </template>
              </p>
              <p v-if="item.certification_can_use === 1">{{lang.coin_text48}}</p>
              <p v-if="item.with_event_promotion_use === 0">{{lang.coin_text49}}</p>
              <p v-if="item.with_promo_code_use === 0">{{lang.coin_text50}}</p>
              <p v-if="item.with_client_level_use === 0">{{lang.coin_text51}}</p>
              <p v-if="item.with_voucher_use === 0">{{lang.coin_text52}}</p>
              <template
                v-if="item.order_use_limit == 'product' || item.single_consume_use_limit == 'product' || item.full_gift_use_limit == 'product' || item.type == 'default' || item.type == 'property' || item.type == 'recharge' || item.type == 'total_consume'">
                <p v-if="item.product_ids_arr?.length > 0">
                  {{lang.coin_text47}}：
                  <span v-for="(el,index) in item.product_ids_arr" :key="el.id">
                    <a :href="'/cart/goods.htm?id=' + el.id" target="_blank"
                      style="color: var(--color-primary);">{{el.name}}</a>
                    <span v-if="index !== item.product_ids_arr.length - 1">、</span>
                  </span>
                </p>
                <p v-else>
                  {{lang.coin_text74}}
                </p>
              </template>
              <p
                v-if="item.order_use_limit == 'host' || item.single_consume_use_limit == 'host' || item.full_gift_use_limit == 'host'">
                {{lang.coin_text130}}{{coin_name}}{{lang.coin_text131}}
              </p>
              <p v-if="item.product_only_defence == 1">{{lang.coin_text65}}</p>
              <p
                v-if="item.order_available == 1 || item.upgrade_available == 1 || item.renew_available == 1 || item.demand_available == 1">
                {{lang.coin_text132}}：
                <template v-if="item.order_available == 1">
                  {{lang.coin_text133}}<span
                    v-if="item.upgrade_available == 1 || item.renew_available == 1 || item.demand_available == 1">、</span>
                </template>
                <template v-if="item.renew_available == 1">
                  {{lang.coin_text134}}<span v-if="item.upgrade_available == 1 || item.demand_available == 1">、</span>
                </template>
                <template v-if="item.upgrade_available == 1">
                  {{lang.coin_text135}}<span v-if="item.demand_available == 1">、</span>
                </template>
                <template v-if="item.demand_available == 1">
                  {{lang.coin_text136}}
                </template>
              </p>
              <p v-if="item.cycle_limit === 1 ">
                {{lang.coin_text5}}：<span v-for="(cycle_item,index) in item.cycle"
                  :key="cycle_item">{{lang[cycle_item]}}
                  <span v-if="index !== item.cycle.length - 1">、</span></span>
              </p>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</el-popover>
`,
  data() {
    return {
      commonData: {},
      coin_name: "",
      coinClientInfo: {},
      coinActiveList: [],
      loading: false,
      home_show_coin_activity: 0,
      activeTab: "current",
      product_id: 0,
      host_id: 0,
      active_show: false,
      init: true,
    };
  },

  filters: {
    formateTime(time) {
      if (time && time !== 0) {
        return formateDate(time * 1000, "YYYY-MM-DD HH:mm");
      } else {
        return lang.voucher_effective;
      }
    },
  },
  created() {
    if (!havePlugin("Coin")) {
      return;
    }
    // 加载css
    if (
      !document.querySelector(
        'link[href="' + url + 'components/coinActive/coinActive.css"]'
      )
    ) {
      const link = document.createElement("link");
      link.rel = "stylesheet";
      link.href = `${url}components/coinActive/coinActive.css`;
      document.head.appendChild(link);
    }
    this.commonData =
      JSON.parse(localStorage.getItem("common_set_before")) || {};
    this.getCoinInfo();
  },
  methods: {
    visbleShow() {
      this.coinActiveList.forEach((item) => {
        item.show_desc = false;
      });
      this.getCoinActiveList();
    },
    handleTabClick() {
      this.getCoinActiveList();
    },
    // 计算场景送的活动范围
    calculateSceneRange(item) {
      const allActions = [];
      item.forEach(rule => {
        rule.notice_actions_arr.forEach(action => {
          if (action.name === 'host_renew') {
            allActions.push(`${rule.trigger_products[0] ? rule.trigger_products[0].name : lang.coin_text108}${action.name_lang}：${lang.coin_text105}${rule.amount}${this.coin_name}`);
          } else if (action.name === 'order_pay') {
            allActions.push(`${rule.trigger_products[0] ? rule.trigger_products[0].name : lang.coin_text108}${action.name_lang}：${lang.coin_text105}${rule.amount}${this.coin_name}`);
          } else {
            if (!allActions.includes(action)) {
              allActions.push(`${action.name_lang}：${lang.coin_text105}${rule.amount}${this.coin_name}`);
            }
          }
        });
      });
      return allActions;
    },
    setProductId() {
      const nowUrl = location.href.split("/").pop();
      const pageRouter =
        nowUrl.indexOf("?") !== -1 ? nowUrl.split("?")[0] : nowUrl;
      if (pageRouter == "goods.htm") {
        this.product_id = getUrlParams().id;
      } else if (pageRouter == "productdetail.htm") {
        this.host_id = getUrlParams().id;
      }
    },
    async getCoinInfo() {
      try {
        const res = await apiCoinClientCoupon();
        this.coin_name = res.data.data.name;
        this.coinClientInfo = res.data.data;
        this.home_show_coin_activity = res.data.data.home_show_coin_activity;
        if (this.home_show_coin_activity == 1) {
          this.setProductId();
          await this.getCoinActiveList(true);
        }
      } catch (error) {
        console.log(error);
      }
    },
    async getCoinActiveList(isInit = false) {
      this.loading = true;
      const params = {};
      const isProduct = this.activeTab === "current" && this.product_id;
      const isHost = this.activeTab === "current" && this.host_id;
      params.product_id = isProduct ? this.product_id : undefined;
      params.host_id = isHost ? this.host_id : undefined;
      const isGoodPage = isProduct || isHost;
      await apiCoinActiveList(params)
        .then((res) => {
          this.coinActiveList = res.data.data.list.map((item) => {
            item.show_desc = false;
            return item;
          });
          if (isGoodPage && isInit === true) {
            this.active_show = this.coinActiveList.length > 0;
          }
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
};

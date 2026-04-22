/* 以用户登录 */
const loginByUser = {
  template: ` <t-popup overlay-inner-class-name="login-by-user" placement="right"   v-model:visible="visible"
      :disabled="!$checkPermission('auth_user_detail_personal_information_user_login')"
        @visible-change="getInfo">
        <slot></slot>
        <template #content>
          <t-loading size="small" :loading="loading" showOverlay>
            <div class="user-info">
              <div class="top">
                <p class="username">{{user.username}}</p>
                <p class="level" v-if="levelName">{{levelName}}</p>
              </div>
              <p class="user-id">
                <span class="id">ID</span>{{user.id}}
              </p>
              <div class="info">
                <p class="item">
                  <span class="label">{{lang.credit}}：</span>
                  <span class="value">{{common_set.currency_prefix}}{{user.credit}}</span>
                </p>
                <p class="item">
                  <span class="label">{{lang.consume}}：</span>
                  <span class="value">{{common_set.currency_prefix}}{{user.consume || 0.00}}</span>
                </p>
                <p class="item">
                  <span class="label">{{lang.sales}}：</span>
                  <span class="value">{{saleName}}</span>
                </p>
                <p class="item">
                  <span class="label">{{lang.company}}：</span>
                  <span class="value" :title="user.company">{{user.company || '--'}}</span>
                </p>
                <p class="item">
                  <span class="label">{{lang.phone}}：</span>
                  <span class="value" v-if="user.phone" :title="user.phone">+{{user.phone_code}}-{{user.phone}}</span>
                  <span v-else>--</span>
                </p>
                <p class="item">
                  <span class="label">{{lang.email}}：</span>
                  <span class="value" :title="user.email">{{user.email || '--'}}</span>
                </p>
                <p class="item">
                  <span class="label">{{lang.register_time}}：</span>
                  <span class="value">{{ user.register_time ? moment(user.register_time * 1000).format('YYYY-MM-DD') : '--' }}</span>
                </p>
                <p class="item">
                  <span class="label">{{lang.last_login_time}}：</span>
                  <span class="value">{{ user.last_login_time ? moment(user.last_login_time * 1000).format('YYYY-MM-DD') : '--' }}</span>
                </p>
                <p class="item all">
                  <span class="label">{{lang.notes}}：</span>
                  <span v-show="!isEdit" class="textarea">
                    {{user.notes}}
                    <t-icon name="edit" @click="handelEdit" class="edit-notes"></t-icon>
                  </span>
                  <t-textarea v-show="isEdit" ref="textarea" v-model="user.notes" :autofocus="true" @blur="saveUser"></t-textarea>
                </p>
              </div>
            </div>
          </t-loading>
          <t-button theme="primary" @click="commonLoginByUser"
            v-permission="'auth_user_detail_personal_information_user_login'">
            {{lang.login_as_user}}
          </t-button>
        </template>
      </t-popup>
    `,
  data() {
    return {
      visible: false,
      isEdit: false,
      user: {},
      loading: false,
      common_set: JSON.parse(localStorage.getItem("common_set")) || {},
      levelName: "",
      saleName: "--",
      tempNotes: "",
    };
  },
  computed: {},
  watch: {
    visible(val) {
      if (!val && this.isEdit) {
        this.visible = true;
      }
    },
  },
  props: {
    id: {
      type: Number,
      required: true,
      default: null,
    },
    website_url: {
      type: String,
      required: true,
      default: "",
    },
  },
  created() {},
  methods: {
    handelEdit() {
      this.isEdit = true;
      setTimeout(() => {
        this.$refs.textarea.focus();
      }, 0);
    },
    async saveUser() {
      try {
        this.isEdit = false;
        if (
          (!this.tempNotes && !this.user.notes) ||
          this.tempNotes === this.user.notes
        ) {
          return;
        }
        const res = await updateClient(this.id, this.user);
        this.$message.success(res.data.msg);
        this.getUserDetails();
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
    getInfo(bol) {
      bol && this.getUserDetails();
    },
    async getUserDetails() {
      try {
        this.loading = true;
        const res = await getClientDetail(this.id);
        this.user = res.data.data.client;
        this.tempNotes = JSON.parse(JSON.stringify(this.user)).notes;
        this.loading = false;
        const levelObj = this.user.customfield?.idcsmart_client_level || {};
        if (levelObj.id) {
          this.levelName = levelObj.list.filter(
            (item) => item.id === levelObj.id
          )[0]?.name;
        }
        const sale = this.user.customfield?.idcsmart_sale || {};
        if (sale.id) {
          this.saleName =
            sale.list?.filter((item) => item.id === sale.id)[0]?.name || "--";
        }
      } catch (error) {
        this.loading = false;
        this.$message.error(error.data.msg);
      }
    },
    // 以用户登录
    async commonLoginByUser() {
      try {
        const opener = window.open("", "_blank");
        const res = await loginByUserId(this.id);
        localStorage.setItem("jwt", res.data.data.jwt);
        localStorage.setItem("boxJwt", res.data.data.jwt);
        const url = `${this.website_url}/home.htm?queryParam=${res.data.data.jwt}`;
        opener.location = url;
      } catch (error) {
        this.$message.error(error.data.msg);
      }
    },
  },
};

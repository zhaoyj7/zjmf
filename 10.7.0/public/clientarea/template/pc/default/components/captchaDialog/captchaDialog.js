// 验证码通过 - 找到当前显示的验证码实例
function captchaCheckSuccsss(bol, captcha, token) {
  if (bol) {
    // 遍历所有注册的验证码实例，找到 DOM 中存在的那个（v-if 确保存在即可见）
    if (window.captchaInstances) {
      for (let captchaId in window.captchaInstances) {
        const element = document.getElementById(captchaId);
        // v-if 保证：元素存在 = 元素可见
        if (element) {
          const instance = window.captchaInstances[captchaId];
          if (instance && instance.getData) {
            instance.getData(captcha, token);
            break; // 找到就退出循环
          }
        }
      }
    }
  }
}

// 取消验证码验证 - 找到当前显示的验证码实例
function captchaCheckCancel() {
  if (window.captchaInstances) {
    for (let captchaId in window.captchaInstances) {
      const element = document.getElementById(captchaId);
      // v-if 保证：元素存在 = 元素可见
      if (element) {
        const instance = window.captchaInstances[captchaId];
        if (instance && instance.captchaCancel) {
          instance.captchaCancel();
          break; // 找到就退出循环
        }
      }
    }
  }
}

// css 样式依赖common.css
const captchaDialog = {
  template: `
    <div :id="captchaId" v-if="isShowCaptcha"></div>`,
  created() {
    // this.doGetCaptcha()
  },
  data() {
    return {
      captchaData: {
        token: "",
        captcha: "",
      },
      captchaHtml: "",
    };
  },
  props: {
    isShowCaptcha: {
      type: Boolean,
    },
    captchaId: {
      type: String,
      default() {
        // 生成唯一 ID，避免多实例冲突
        return `captchaHtml_${Date.now()}_${Math.random()
          .toString(36)
          .slice(2, 11)}`;
      },
    },
  },
  methods: {
    // 获取图形验证码
    doGetCaptcha() {
      try {
        getNewCaptcha().then((res) => {
          if (res.data.status === 200) {
            this.captchaHtml = res.data.data.html;
            $(`#${this.captchaId}`).html(this.captchaHtml);
            $(`#${this.captchaId}`).show();
          }
        });
      } catch (e) {
        console.log("获取图形验证码", e);
      }
    },
    // 取消验证码
    captchaCancel() {
      this.$emit("captcha-cancel");
    },
    // 获取验证码数据
    getData(captchaCode, token) {
      this.captchaData.captcha = captchaCode;
      this.captchaData.token = token;
      this.$emit("get-captcha-data", captchaCode, token);
    },
  },
  mounted() {
    // 创建全局实例映射对象
    if (!window.captchaInstances) {
      window.captchaInstances = {};
    }

    // 将当前实例注册到全局映射表中，使用箭头函数保持 this 指向
    window.captchaInstances[this.captchaId] = {
      getData: (captchaCode, token) => {
        this.getData(captchaCode, token);
      },
      captchaCancel: () => {
        this.captchaCancel();
      },
    };
  },
  // 组件销毁时清理对应的实例引用
  beforeDestroy() {
    if (window.captchaInstances && window.captchaInstances[this.captchaId]) {
      delete window.captchaInstances[this.captchaId];
    }
  },
};

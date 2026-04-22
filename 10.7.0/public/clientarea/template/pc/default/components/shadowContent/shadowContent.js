const shadowContent = {
  template: /* html*/ `
    <div ref="shadow"></div>
    `,
  props: {
    content: {
      type: String,
      default: "",
    },
  },
  data() {
    return {
      shadowRoot: null,
    };
  },
  mounted() {
    this.shadowRoot = this.$refs.shadow.attachShadow({mode: "open"});
    this.renderShadow();
  },
  // 内容变动时更新 Shadow DOM
  watch: {
    content() {
      this.renderShadow();
    },
  },
  methods: {
    renderShadow() {
      this.shadowRoot.innerHTML = `
        <style>
          img {
          max-width: 100%;
          height: auto;
          }
        </style>
        ${this.content}
      `;
    },
  },
};

/* 以用户登录 */
const comViewimg = {
  template: `
    <div style="width: 0; height: 0;">
      <img id="viewerImg" style="display: none;" :src="preImg" alt="">
    </div>
    `,
  data() {
    return {
      viewer: null,
      preImg: "",
    };
  },
  mounted() {
    this.loadViewer();
  },
  methods: {
    loadViewer(isInit = false) {
      if (!this.viewer) {
        const link = document.createElement("link");
        link.rel = "stylesheet";
        link.href = `${url}css/common/viewer.min.css`;
        document.head.appendChild(link);
        const script = document.createElement("script");
        script.src = `${url}js/common/viewer.min.js`;
        document.head.appendChild(script);
        script.onload = () => {
          this.viewer = new Viewer(document.getElementById("viewerImg"), {
            button: true,
            inline: false,
            zoomable: true,
            title: true,
            tooltip: true,
            minZoomRatio: 0.5,
            maxZoomRatio: 100,
            movable: true,
            interval: 2000,
            navbar: true,
            loading: true,
          });
          if (isInit) {
            this.viewer.show();
          }
        };
      } else {
        if (isInit) {
          this.viewer.show();
        }
      }
    },
    showViewer(url) {
      this.preImg = url;
      if (this.viewer) {
        this.viewer.show()
      } else {
        this.loadViewer(true);
      }
    },
  },
};

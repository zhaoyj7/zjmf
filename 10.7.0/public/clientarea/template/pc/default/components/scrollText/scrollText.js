const scrollText = {
  template: /* html*/ `
  <div class="scroll-container" ref="container">
  <div
    class="scroll-text"
    ref="text"
    :class="{ 'centered': !isOverflow }"
    :style="textStyle"
    @mouseenter="pause"
    @mouseleave="resume"
  >
    <slot></slot>
    <!-- loop 模式下复制一份实现无缝 -->
    <span v-if="mode === 'loop' && isOverflow" class="clone">
      <slot></slot>
    </span>
  </div>
</div>
            `,
  data() {
    return {
      duration: 0,
      isOverflow: false,
      paused: false,
      containerWidth: 0,
      textWidth: 0,
    };
  },
  props: {
    speed: {type: Number, default: 50}, // 像素/秒
    pauseTime: {type: Number, default: 2}, // once 模式停顿时间(秒)
    mode: {
      type: String,
      default: "loop", // 可选: loop, once, pause
    },
  },

  computed: {
    textStyle() {
      // 不超出：静止居中
      if (!this.isOverflow) {
        return {transform: "translateX(0)"};
      }

      let animation = "";
      if (this.mode === "loop") {
        animation = `scroll-loop ${this.duration}s linear infinite`;
      } else if (this.mode === "once") {
        // 每次滚动结束，停 pauseTime 秒
        const totalTime = this.duration + this.pauseTime;
        animation = `scroll-once ${totalTime}s linear infinite`;
      } else if (this.mode === "pause") {
        animation = `scroll-pause ${this.duration}s linear forwards`;
      }

      return {
        animation,
        "animation-play-state": this.paused ? "paused" : "running",
      };
    },
  },
  mounted() {
    this.checkOverflow();
    window.addEventListener("resize", this.checkOverflow);
  },
  beforeDestroy() {
    window.removeEventListener("resize", this.checkOverflow);
  },
  methods: {
    checkOverflow() {
      this.$nextTick(() => {
        const container = this.$refs.container;
        const text = this.$refs.text;
        if (!container || !text) return;

        this.containerWidth = container.clientWidth;
        this.textWidth = text.scrollWidth;

        this.isOverflow = this.textWidth > this.containerWidth;

        if (this.isOverflow) {
          this.duration = this.textWidth / this.speed;
        }
      });
    },
    pause() {
      this.paused = true;
    },
    resume() {
      this.paused = false;
    },
  },
};

(function (window, undefined) {
  var old_onload = window.onload;
  window.onload = function () {
    const template = document.getElementsByClassName("home-drag")[0];
    Vue.prototype.lang = window.lang;
    new Vue({
      components: {
        comConfig,
      },
      data() {
        return {
          templateList: [],
          colNum: 12, // 定义栅格系统的列数
          rowHeight: 10,
          allWidget: [],
          checkWidget: [],
          showList: [
            {
              i: 1,
              w: 8,
              minW: 8,
            },
            {
              i: 2,
              w: 4,
              minW: 4,
            },
            {
              i: 3,
              w: 12,
              minW: 12,
            },
            {
              i: 4,
              w: 4,
              minW: 4,
            },
            {
              i: 5,
              w: 8,
              minW: 8,
            },
          ],
          loading: false,
          showIndexPage: false,
          userName: localStorage.getItem("userName"),
          firstNav: [],
          authList: [],
          timer: null,
          myChart: null,
        };
      },
      mounted() {
        this.initChart();
      },
      methods: {
        // 添加调整图表大小的方法
        resizeChart() {
          if (this.myChart) {
            this.myChart.resize();
          }
        },
        onStart() {
          this.showList.forEach((item) => {
            item.w = item.minW;
          });
        },
        async onEnd(e) {
          try {
            this.adjustList();
            // 在布局调整后重新调整图表大小
            this.$nextTick(() => {
              this.resizeChart();
            });
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        adjustList() {
          // 1. 计算行数和每行元素
          let currentRowWidth = 0;
          let currentRow = 0;
          let rowGroups = [[]]; // 用于存储每行的元素
          this.showList.forEach((item, index) => {
            // 先计算当前行加上新元素后的总宽度
            const newRowWidth = currentRowWidth + item.w;

            // 如果加上新元素后超过12，则换行
            if (newRowWidth > 12) {
              currentRow++;
              currentRowWidth = item.w; // 新行从当前元素开始
              rowGroups[currentRow] = [item]; // 创建新行并添加当前元素
            } else {
              currentRowWidth = newRowWidth; // 继续在当前行累加
              rowGroups[currentRow].push(item); // 将元素添加到当前行
            }
          });
          rowGroups.forEach((row, rowIndex) => {
            // 判断每行的总宽度是否为12
            const totalWidth = row.reduce((sum, item) => sum + item.w, 0);
            if (totalWidth !== 12) {
              // 如果不等于12，则需要调整当前行的元素宽度
              const adjustment = (12 - totalWidth) / row.length; // 计算每个元素需要增加的宽度
              row.forEach((item) => {
                item.w += adjustment; // 调整每个元素的宽度
              });
            }
          });
        },
        layoutReadyEvent() {
          this.initChart();
        },
        initChart() {
          this.myChart = echarts.init(document.getElementById("ThisYearSale"));
          const option = {
            xAxis: {
              type: "category",
              data: [
                "1月",
                "2月",
                "3月",
                "4月",
                "5月",
                "6月",
                "7月",
                "8月",
                "9月",
                "10月",
                "11月",
                "12月",
              ],
              axisLine: {
                lineStyle: {
                  color: "#E5E5E5",
                },
              },
              axisTick: {
                show: false,
              },
              axisLabel: {
                color: "#646464",
                backgroundColor: "#fff",
                borderRadius: "8px",
                padding: [4, 8],
                boxSizing: "border-box",
                rich: {
                  value: {
                    backgroundColor: "#fff",
                    borderColor: "#E5E5E5",
                    borderWidth: 1,
                    borderRadius: 8,
                    padding: [4, 8],
                    fontSize: 14,
                  },
                },
                formatter: function (value) {
                  return "{value|" + value + "}";
                },
              },
            },
            tooltip: {
              trigger: "axis",
              axisPointer: {
                type: "none",
              },
              formatter: function (params) {
                const value = params[0].value;
                return `<div style="display: flex; align-items: center;">
                  <span style="display: inline-block; width: 8px; height: 8px; background-color: #3979f1; border-radius: 50%; margin-right: 8px;"></span>
                  <span>销售额：</span>
                  <span style="color: #3979f1; font-weight: 600;">${value}</span>
                </div>`;
              },
            },
            yAxis: {
              type: "value",
              axisLine: {
                lineStyle: {
                  type: "dashed",
                },
              },
              splitLine: {
                lineStyle: {
                  type: "dashed",
                },
              },
            },
            grid: {
              left: "0%",
              right: "0%",
              bottom: "5%",
              top: "5%",
              containLabel: true,
            },
            series: [
              {
                data: [
                  120, 200, 150, 80, 70, 110, 130, 100, 140, 150, 160, 170,
                ],
                type: "bar",
                color: "#F9FAFC",
                itemStyle: {
                  borderRadius: [16, 16, 0, 0],
                },
                emphasis: {
                  itemStyle: {
                    color: "#3979f1",
                  },
                },
              },
            ],
          };
          this.myChart.setOption(option);
          this.$nextTick(() => {
            this.resizeChart();
          });
        },
        async getWidgetList() {
          try {
            this.loading = true;
            const res = await this.mockWidget();
            this.loading = false;
          } catch (error) {
            this.loading = false;
            this.$message.error(error.data.msg);
          }
        },
        async getContent(widget) {
          try {
            const res = await getWidgetContent({widget});
            this.$nextTick(() => {
              $(`#${widget}`).html(res.data.data.content);
            });
          } catch (error) {
            this.$message.error(error.data.msg);
          }
        },
        layoutUpdatedEvent(newLayout) {
          // this.showList = newLayout;
        },
        movedEvent(i, newX, newY) {
          console.log("MOVED i=" + i + ", X=" + newX + ", Y=" + newY);
        },
      },
    }).$mount(template);
    typeof old_onload == "function" && old_onload();
  };
})(window);

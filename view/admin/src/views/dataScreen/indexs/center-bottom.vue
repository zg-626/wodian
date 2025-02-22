<template>
  <div class="center_bottom">
    <Echart
      :options="options"
      id="bottomLeftChart"
      class="echarts_bottom"
    ></Echart>
  </div>
</template>

<script>
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import { currentGET } from "@/api/screen";
import { graphic } from "echarts";
export default {
  data() {
    return {
      options: {},
      timer: null,
    };
  },
  props: {},
  mounted() {
    this.getData();
  },
  beforeDestroy() {
    this.clearData();
  },
  methods: {
    clearData() {
      if (this.timer) {
        clearInterval(this.timer);
        this.timer = null;
      }
    },
    //轮询
    switper() {
      if (this.timer) {
        return;
      }
      let looper = (a) => {
        this.getData();
      };
      this.timer = setInterval(
        looper,
        this.$store.state.screenSetting.tableDataAutoTime
      );
    },
    getData() {
      this.pageflag = true;
      currentGET("month_pay_count")
        .then((res) => {
          let data = {
            category: [],
            lineData: [],
          };
          res.data.month_pay_count.map((item) => {
            data.lineData.push(item.total_sum);
            data.category.push(item.day);
          });
          this.init(data);
          // this.init(res.data.month_pay_count);\
          this.switper();
        })
        .catch((err) => {
          this.pageflag = false;
          this.$message({
            text: err,
            type: "warning",
          });
        });
    },
    init(newData) {
      this.options = {
        tooltip: {
          trigger: "axis",
          backgroundColor: "rgba(0,0,0,.6)",
          borderColor: "rgba(147, 235, 248, .8)",
          textStyle: {
            color: "#FFF",
          },
          formatter: function (params) {
            // 添加单位
            var result = "日期: " + params[0].name + "<br>";
            params.forEach(function (item) {
              // if (item.value) {
              //   if (item.seriesName == "成交率") {
              //     result +=
              //       item.marker +
              //       " " +
              //       item.seriesName +
              //       " : " +
              //       item.value +
              //       "%</br>";
              //   } else {
              //     result +=
              //       item.marker +
              //       " " +
              //       item.seriesName +
              //       " : " +
              //       item.value +
              //       "个</br>";
              //   }
              // } else {
              // }
              result += "金额: " + item.value + "元</br>";
            });
            return result;
          },
        },
        legend: {
          data: ["已成交"],
          textStyle: {
            color: "#B4B4B4",
          },
          top: "0",
        },
        grid: {
          left: "50px",
          right: "40px",
          bottom: "30px",
          top: "20px",
        },
        xAxis: {
          data: newData.category,
          axisLine: {
            lineStyle: {
              color: "#33D3FF",
            },
          },
          axisTick: {
            show: false,
          },
        },
        yAxis: [
          {
            splitLine: { show: false },
            axisLine: {
              lineStyle: {
                color: "#33D3FF",
              },
            },

            axisLabel: {
              formatter: "{value}",
            },
          },
          // {
          //   splitLine: { show: false },
          //   axisLine: {
          //     lineStyle: {
          //       color: "#B4B4B4",
          //     },
          //   },
          //   axisLabel: {
          //     formatter: "{value}% ",
          //   },
          // },
        ],
        series: [
          // {
          //   name: "已成交",
          //   type: "bar",
          //   barWidth: 10,
          //   itemStyle: {
          //     borderRadius: 5,
          //     color: new graphic.LinearGradient(0, 0, 0, 1, [
          //       { offset: 0, color: "#956FD4" },
          //       { offset: 1, color: "#3EACE5" },
          //     ]),
          //   },
          //   data: newData.barData,
          // },
          {
            name: "销售额",
            type: "bar",
            barGap: "-100%",
            barWidth: 10,
            itemStyle: {
              borderRadius: 5,
              color: new graphic.LinearGradient(0, 0, 0, 1, [
                { offset: 0, color: "rgba(156,107,211,0.8)" },
                { offset: 0.2, color: "rgba(156,107,211,0.5)" },
                { offset: 1, color: "rgba(156,107,211,0.2)" },
              ]),
            },
            z: -12,
            data: newData.lineData,
          },
          // {
          //   name: "成交率",
          //   type: "line",
          //   smooth: true,
          //   showAllSymbol: true,
          //   symbol: "emptyCircle",
          //   symbolSize: 8,
          //   yAxisIndex: 1,
          //   itemStyle: {
          //     color: "#F02FC2",
          //   },
          //   data: newData.rateData,
          // },
        ],
      };
    },
  },
};
</script>
<style lang="scss" scoped>
.center_bottom {
  width: 100%;
  height: 100%;

  .echarts_bottom {
    width: 100%;
    height: 100%;
  }
}
</style>

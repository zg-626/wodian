<template>
  <div class="centermap">
    <div class="count-title">
      <div class="zuo"></div>
      <span class="titletext">今日订单支付金额(元)</span>
      <div class="you"></div>
    </div>
    <div class="maptitle">
      <span class="titletext">{{ today_pay_number || 0.00 }}</span>
    </div>
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
import { currentGET, GETNOBASE } from "@/api/screen";

export default {
  data() {
    return {
      maptitle: "商品销售统计",
      options: {},
      code: "china", //china 代表中国 其他地市是行政编码
      echartBindClick: false,
      isSouthChinaSea: false, //是否要展示南海群岛  修改此值请刷新页面
      today_pay_number: "0.00",
      timer: null,
    };
  },
  created() {},

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
        console.log("looper");
        this.getData();
      };
      this.timer = setInterval(
        looper,
        this.$store.state.screenSetting.countNumberAutoTime
      );
    },
    getData() {
      currentGET("today_pay_number")
        .then((res) => {
          console.log("今日支付", res);
          this.today_pay_number = res.data.today_pay_number.number;
          this.switper();
        })
        .catch((err) => {
          this.$Message.warning(err.msg);
        });
    },
    message(text) {
      this.$Message({
        text: text,
        type: "warning",
      });
    },
  },
};
</script>
<style lang="scss" scoped>
.centermap {
  margin: 30px 0 0;
  .count-title {
    display: flex;
    justify-content: center;
    box-sizing: border-box;
    .titletext {
      font-size: 14px;
      font-weight: 900;
    }
    .zuo,
    .you {
      background-size: 100% 100%;
      width: 20px;
      height: 11px;
      margin: 5px 12px 0 12px;
    }
  }
  .maptitle {
    height: 90px;
    display: flex;
    justify-content: center;
    padding-top: 32px;
    box-sizing: border-box;

    .titletext {
      font-family: "number-font";
      font-size: 80px;
      font-weight: 900;
      line-height: 60px;
      letter-spacing: 4px;
      background: linear-gradient(
        92deg,
        #0072ff 0%,
        #00eaff 48.8525390625%,
        #01aaff 100%
      );
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin: 0 10px;
    }

    .zuo,
    .you {
      background-size: 100% 100%;
      width: 29px;
      height: 20px;
      margin-top: 8px;
    }
  }
  .zuo {
    background: url("../assets/img/xiezuo.png") no-repeat;
  }

  .you {
    background: url("../assets/img/xieyou.png") no-repeat;
  }

  .mapwrap {
    height: 508px;
    width: 100%;
    // padding: 0 0 10px 0;
    box-sizing: border-box;
    position: relative;

    .quanguo {
      position: absolute;
      right: 20px;
      top: -46px;
      width: 80px;
      height: 28px;
      border: 1px solid #00eded;
      border-radius: 10px;
      color: #00f7f6;
      text-align: center;
      line-height: 26px;
      letter-spacing: 6px;
      cursor: pointer;
      box-shadow: 0 2px 4px rgba(0, 237, 237, 0.5),
        0 0 6px rgba(0, 237, 237, 0.4);
    }
  }
}
</style>

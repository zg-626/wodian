<template>
  <div class="right_bottom">
    <!-- <dv-capsule-chart :config="config" style="width: 100%; height: 100%" /> -->
    <dv-scroll-ranking-board
      :config="config"
      style="width: 100%; height: 100%"
      :waitTime="2000"
    />
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
export default {
  data() {
    return {
      gatewayno: "",
      config: {
        showValue: true,
        unit: "次",
        data: [],
      },
    };
  },
  created() {
    this.getData();
  },
  computed: {},
  mounted() {},
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
        this.$store.state.screenSetting.echartsAutoTime
      );
    },
    getData() {
      this.pageflag = true;
      // this.pageflag =false
      currentGET("today_pay_merchant_rank")
        .then((res) => {
          if (!this.timer) {
            console.log("商户排名", res);
          }
          this.config.unit = res.data.today_pay_merchant_rank.type;
          this.config = {
            ...this.config,
            data: res.data.today_pay_merchant_rank.data,
          };
          this.switper();
        })
        .catch((err) => {
          this.pageflag = false;
          this.srcList = [];
          this.$message({
            text: err,
            type: "warning",
          });
        });
    },
  },
};
</script>
<style lang='scss' scoped>
.list_Wrap {
  height: 100%;
  overflow: hidden;
  :deep(.kong) {
    width: auto;
  }
}
::v-deep .dv-scroll-ranking-board .ranking-info .rank{
  color: #ffba1a;
  width: 80px;
}
::v-deep .dv-scroll-ranking-board .ranking-column .inside-column{
  background-color: #33D3FF;
}
.sbtxSwiperclass {
  .img_wrap {
    overflow-x: auto;
  }
}
.right_bottom {
  box-sizing: border-box;
  padding: 0 10px;
  height: 100%;
  .searchform {
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    .searchform_item {
      display: flex;
      justify-content: center;
      align-items: center;
      label {
        margin-right: 10px;
        color: rgba(255, 255, 255, 0.8);
      }
      button {
        margin-left: 30px;
      }
    }
  }
  .img_wrap {
    display: flex;
    box-sizing: border-box;
    padding: 0 0 20px;
    li {
      width: 105px;
      height: 137px;
      border-radius: 6px;
      overflow: hidden;
      cursor: pointer;
      overflow: hidden;
      flex-shrink: 0;
      margin: 0 10px;
      img {
        flex-shrink: 0;
      }
    }
  }
  .noData {
    width: 100%;
    line-height: 100px;
    text-align: center;
    color: rgb(129, 128, 128);
  }
}
</style>

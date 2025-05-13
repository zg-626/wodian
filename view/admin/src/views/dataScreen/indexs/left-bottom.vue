<template>
  <div
    v-if="pageflag"
    class="left_boottom_wrap beautify-scroll-def"
    :class="{ 'overflow-y-auto': !sbtxSwiperFlag }"
  >
    <component :is="components" :data="list" :class-option="defaultOption">
      <ul class="left_boottom">
        <li class="left_boottom_item" v-for="(item, i) in list" :key="i">
          <span class="orderNum doudong">No{{ i + 1 }}</span>
          <div class="inner_right">
            <div class="dibu"></div>
            <div class="info addresswrap wangguan">
              <span class="labels">商品：</span>
              <span class="contents zhuyao" style="font-size: 12px">
                {{ item.product.store_name }}</span
              >
            </div>
            <div class="flex justify-between w-100">
              <!-- <div class="info">
                <span class="labels">商品：</span>
                <span class="contents doudong wangguan">
                  {{ item.product.store_name }}</span
                >
              </div> -->
              <div class="info">
                <span class="labels">支付时间：</span>
                <span class="contents" style="font-size: 12px">
                  {{ item.paytime }}</span
                >
              </div>
              <div class="info">
                <span class="labels">支付金额：</span>
                <span class="contents" style="font-size: 12px">
                  {{ item.number }}</span
                >
              </div>
            </div>

            <!-- <span
              class="types doudong"
              :class="{
                typeRed: item.onlineState == 0,
                typeGreen: item.onlineState == 1,
              }"
              >{{ item.onlineState == 1 ? "上线" : "下线" }}</span
            > -->
          </div>
        </li>
      </ul>
    </component>
  </div>

  <Reacquire v-else @onclick="getData" style="line-height: 200px" />
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
import vueSeamlessScroll from "vue-seamless-scroll"; // vue2引入方式
import Kong from "../components/kong.vue";
export default {
  components: { vueSeamlessScroll, Kong },
  data() {
    return {
      list: [],
      pageflag: true,
      components: vueSeamlessScroll,
      defaultOption: {
        ...this.$store.state.screenSetting.defaultOption,
        singleHeight: 240,
        limitMoveNum: 2,
        step: 0,
      },
      timer: null,
    };
  },
  computed: {
    sbtxSwiperFlag() {
      let sbtxSwiper = this.$store.state.screenSetting.sbtxSwiper;
      if (sbtxSwiper) {
        this.components = vueSeamlessScroll;
      } else {
        this.components = Kong;
      }
      return sbtxSwiper;
    },
  },
  created() {},

  mounted() {
    this.getData();
    let timer = setTimeout(() => {
      clearTimeout(timer);
      this.defaultOption.step =
        // this.$store.state.screenSetting.defaultOption.step;
        .5
    }, this.$store.state.screenSetting.defaultOption.waitTime);
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
    addressHandle(item) {
      let name = item.provinceName;
      if (item.cityName) {
        name += "/" + item.cityName;
        if (item.countyName) {
          name += "/" + item.countyName;
        }
      }
      return name;
    },
    getData() {
      this.pageflag = true;
      // this.pageflag =false
      currentGET("today_pay_info")
        .then((res) => {
          console.log("商品提醒", res);
          this.list = res.data.today_pay_info;

          this.$nextTick(() => {
            this.switper();
          });
        })
        .catch((err) => {
          this.pageflag = false;
          this.$Message({
            text: err,
            type: "warning",
          });
        });
    },
  },
};
</script>
<style lang='scss' scoped>
.left_boottom_wrap {
  overflow: hidden;
  width: 100%;
  height: 100%;
}

.doudong {
  //  vertical-align:middle;
  overflow: hidden;
  -webkit-backface-visibility: hidden;
  -moz-backface-visibility: hidden;
  -ms-backface-visibility: hidden;
  backface-visibility: hidden;
}

.overflow-y-auto {
  overflow-y: auto;
}

.left_boottom {
  width: 100%;
  height: 100%;
  .left_boottom_item {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
    font-size: 14px;
    margin: 10px 0;
    .orderNum {
      margin: 0 16px 0 -20px;
      font-size: 12px;
    }

    .info {
      margin-right: 10px;
      display: flex;
      align-items: center;
      color: #fff;

      .labels {
        flex-shrink: 0;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.7);
      }

      .zhuyao {
        color: #33D3FF;
        font-size: 12px;
      }

      .ciyao {
        color: rgba(255, 255, 255, 0.8);
      }

      .warning {
        color: #e6a23c;
        font-size: 15px;
      }
    }

    .inner_right {
      position: relative;
      height: 100%;
      width: 85%;
      flex-shrink: 0;
      line-height: 1;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      .dibu {
        position: absolute;
        height: 1px;
        width: 104%;
        background-image: url("../assets/img/zuo_xuxian.png");
        bottom: -10px;
        left: -2%;
        background-size: cover;
        opacity: .4;
      }
      .addresswrap {
        width: 100%;
        display: flex;
        margin-top: 8px;
      }
    }

    .wangguan {
      color: #33D3FF;
      font-weight: 900;
      font-size: 12px;
      flex-shrink: 0;
      max-width: fit-content;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      margin-bottom: 4px;
    }

    .time {
      font-size: 12px;
      // color: rgba(211, 210, 210,.8);
      color: #fff;
    }

    .address {
      font-size: 12px;
      cursor: pointer;
      // @include text-overflow(1);
    }

    .types {
      width: 30px;
      flex-shrink: 0;
    }

    .typeRed {
      color: #fc1a1a;
    }

    .typeGreen {
      color: #29fc29;
    }
  }
}
</style>

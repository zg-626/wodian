<template>
  <div>
    <div v-if="pageflag"
      class="right_center_title">
      <el-row class="right_center_title_count">
        <el-col :span="3" class="center_title">排名</el-col>
        <el-col :span="21">
          <el-col :span="12" class="center_info">商户信息</el-col>
          <el-col :span="12">
            <el-col :span="12" class="center_sales">销量</el-col>
            <el-col :span="12" class="center_sales">销售金额</el-col>
          </el-col>
          
        </el-col>
        
      </el-row>
    </div>
    <div
      v-if="pageflag"
      class="right_center_wrap beautify-scroll-def"
      :class="{ 'overflow-y-auto': !sbtxSwiperFlag }"
    >
      <component :is="components" :data="list" :class-option="defaultOption">
        <el-row class="right_center">
          <el-row class="right_center_item" v-for="(item, i) in list" :key="i">
            <el-col :span="3" class="orderNum">No{{ i + 1 }}</el-col>
            <el-col :span="21" class="inner_right">
              <el-col :span="12" class="flex flex_name">
                <div class="info acea-row">
                  <div class="contents_info acea-row">
                    <img class="image" :src="item.product.image" alt="">
                    <div class="zhuyao line1">{{ item.product.store_name }}</div>
                  </div>
                </div>
              </el-col>
              <el-col :span="12" class="flex flex_info">
                <el-col :span="12" class="info">

                  <span class="contents ciyao" style="font-size: 12px">
                    {{ item.count || item.number }}
                  </span>
                </el-col>
                <el-col :span="12" class="info time">
                  <span class="contents" style="font-size: 12px">
                    {{ item.number }}</span
                  >
                </el-col>
              </el-col>
            </el-col>
          </el-row>
        </el-row>
      </component>
    </div>
    <Reacquire v-else @onclick="getData" style="line-height: 200px" />
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
import vueSeamlessScroll from "vue-seamless-scroll"; // vue2引入方式
import Kong from "../components/kong.vue";
export default {
  components: { vueSeamlessScroll, Kong },

  data() {
    return {
      list: [],
      pageflag: true,
      defaultOption: {
        ...this.$store.state.screenSetting.defaultOption,
        limitMoveNum: 3,
        singleHeight: 250,
        step: 0,
      },
      timer: null,
    };
  },
  computed: {
    sbtxSwiperFlag() {
      let ssyjSwiper = this.$store.state.screenSetting.ssyjSwiper;
      if (ssyjSwiper) {
        this.components = vueSeamlessScroll;
      } else {
        this.components = Kong;
      }
      return ssyjSwiper;
    },
  },
  created() {
    this.getData();
    let timer = setTimeout(() => {
      clearTimeout(timer);
      this.defaultOption.step = 
        // this.$store.state.screenSetting.defaultOption.step;
        .5
    }, this.$store.state.screenSetting.defaultOption.waitTime);
  },
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
        this.$store.state.screenSetting.tableDataAutoTime
      );
    },
    getData() {
      this.pageflag = true;
      // this.pageflag =false
      currentGET("pay_product_rank")
        .then((res) => {
          console.log("实时预警", res);
          this.list = res.data.pay_product_rank;

          this.$nextTick(() => {
            this.switper();
          });
        })
        .catch((err) => {
          this.pageflag = false;
          this.$message.warning(err.msg);
        });
    },
  },
};
</script>
<style lang='scss' scoped>
.right_center {
  width: 100%;
  height: 100%;
  padding-left: 5px;
  .right_center_item {
    height: auto;
    padding: 10px;
    font-size: 14px;
    color: #fff;
    .orderNum {
      font-size: 14px;
      color: #ffba1a;
    }
    .inner_right {
      position: relative;
      height: 100%;
      line-height: 1.5;
      .dibu {
        position: absolute;
        height: 1px;
        width: 104%;
        background-image: url("../assets/img/zuo_xuxian.png");
        bottom: -12px;
        left: -2%;
        background-size: cover;
      }
    }
    .info {
      display: flex;
      align-items: center;
     
      .labels {
        flex-shrink: 0;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.6);
      }
      .zhuyao {
        color: #ffffff;
        font-size: 12px;
        display: block;
        width: calc(100% - 40px);
      }
      .ciyao {
         color: #ffffff;
      }
      .warning {
        color: #e6a23c;
        font-size: 15px;
      }
    }
  }
}
.right_center_wrap {
  overflow: hidden;
  width: 100%;
  height: 250px;
}
.overflow-y-auto {
  overflow-y: auto;
}
.right_center_title{
  // padding: 0 10px;
  .right_center_title_count{
    display: flex;
    padding: 0 10px;
    color: #33D3FF;
  }
}
.flex_name{
  .info{
    width: 100%;
    .image{
      width: 18px;
      height: 18px;
      border-radius: 2px;
      margin-right: 6px;
    }
    .zhuyao{
      overflow:hidden;
      text-overflow:ellipsis;
      white-space:nowrap
    }
  }
}
.flex_info{  
  background: linear-gradient(90deg, rgba(25,94,181,0) 0%, #195EB5 100%);
}
</style>

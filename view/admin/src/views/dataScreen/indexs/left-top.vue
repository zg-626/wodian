<template>
  <ul class="user_Overview flex" v-if="pageflag">
    <li class="user_Overview-item" style="color: #30f3ff">
      <div class="user_Overview_nums allnum">
        <dv-digital-flop :config="config" style="width: 100%; height: 100%" />
      </div>
      <p>浏览量</p>
    </li>
    <li class="user_Overview-item" style="color: #33ffc3">
      <div class="user_Overview_nums online">
        <dv-digital-flop
          :config="onlineconfig"
          style="width: 100%; height: 100%"
        />
      </div>
      <p>访客数</p>
    </li>
    <li class="user_Overview-item" style="color: #ffba1a">
      <div class="user_Overview_nums offline">
        <dv-digital-flop
          :config="offlineconfig"
          style="width: 100%; height: 100%"
        />
      </div>
      <p>新增用户数</p>
    </li>
    <li class="user_Overview-item" style="color: #ff5ffd">
      <div class="user_Overview_nums laramnum">
        <dv-digital-flop
          :config="laramnumconfig"
          style="width: 100%; height: 100%"
        />
      </div>
      <p>订单数</p>
    </li>
  </ul>
  <Reacquire v-else @onclick="getData" line-height="200px">
    重新获取
  </Reacquire>
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
let style = {
  fontSize: 24,
};
export default {
  data() {
    return {
      options: {},
      userOverview: {
        alarmNum: 0,
        offlineNum: 0,
        onlineNum: 0,
        totalNum: 0,
      },
      pageflag: true,
      timer: null,
      config: {
        number: [100],
        content: "{nt}",
        style: {
          ...style,
          fill: "#30f3ff",
        },
      },
      onlineconfig: {
        number: [0],
        content: "{nt}",
        style: {
          ...style,
          fill: "#33ffc3",
        },
      },
      offlineconfig: {
        number: [0],
        content: "{nt}",
        style: {
          ...style,
          // stroke: "#e3b337",
          fill: "#ffee33",
        },
      },
      laramnumconfig: {
        number: [0],
        content: "{nt}",
        style: {
          ...style,
          // stroke: "#f5023d",
          fill: "#f000ff",
        },
      },
    };
  },
  filters: {
    numsFilter(msg) {
      return msg || 0;
    },
  },
  props: {
    today_pay_count_number: {
      type: Object,
      default: () => {},
    },
  },
  created() {
    this.getData();
  },
  mounted() {},
  beforeDestroy() {
    console.log("关闭了");
    this.clearData();
  },
  methods: {
    clearData() {
      if (this.timer) {
        clearInterval(this.timer);
        this.timer = null;
      }
    },
    getData() {
      this.pageflag = true;
      currentGET("today_pay_count_number")
        .then((res) => {
          console.log(res);
          if (!this.timer) {
            console.log("商品总览", res);
          }
          this.userOverview = res.data.today_pay_count_number;
          const {
            visit_num,
            visit_user_num,
            today_pay_user_first,
            today_pay_number,
          } = res.data.today_pay_count_number;
          this.onlineconfig = {
            ...this.onlineconfig,
            number: [visit_user_num],
          };
          this.config = {
            ...this.config,
            number: [visit_num],
          };
          this.offlineconfig = {
            ...this.offlineconfig,
            number: [today_pay_user_first],
          };
          this.laramnumconfig = {
            ...this.laramnumconfig,
            number: [today_pay_number],
          };
          this.$nextTick((e) => {
            this.switper();
          });
        })
        .catch((err) => {
          this.pageflag = false;
          this.$Message.warning(err.msg);
        });
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
  },
};
</script>
<style lang="scss" scoped>
.user_Overview {
  li {
    flex: 1;

    p {
      text-align: center;
      height: 16px;
      font-size: 14px;
    }

    .user_Overview_nums {
      width: 100px;
      height: 100px;
      text-align: center;
      line-height: 100px;
      font-size: 22px;
      margin: 25px auto 15px;
      background-size: cover;
      background-position: center center;
      position: relative;

      &::before {
        content: "";
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
      }

      &.bgdonghua::before {
        animation: rotating 14s linear infinite;
      }
    }

    .allnum {
      // background-image: url("../assets/img/left_top_lan.png");
      &::before {
        background-image: url("../assets/img/left_top_lan.png");
      }
    }

    .online {
      &::before {
        background-image: url("../assets/img/left_top_lv.png");
      }
    }

    .offline {
      &::before {
        background-image: url("../assets/img/left_top_huang.png");
      }
    }

    .laramnum {
      &::before {
        background-image: url("../assets/img/left_top_hong.png");
      }
    }
  }
}
</style>

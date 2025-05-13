<template>
  <!-- <div id="index" ref="appRef" class="index_home" :class="{ pageisScale: isScale }"> -->
  <ScaleScreen
    :width="1920"
    :height="1080"
    class="scale-wrap"
    :selfAdaption="$store.state.screenSetting.isScale"
  >
    <div class="bg">
      <dv-loading v-if="loading">Loading...</dv-loading>
      <div v-else class="host-body">
        <!-- 头部 s -->
        <div class="d-flex jc-center title_wrap">
          <div class="zuojuxing"></div>
          <div class="youjuxing"></div>
          <div class="guang">{{ dateYear }} {{ dateWeek }} {{ dateDay }}</div>
          <div class="d-flex jc-center">
            <div class="title">
              <span class="title-text">{{screenTitle}}</span>
            </div>
          </div>
          <div class="timers" @click="fullScreen">
            {{isFullScreen() ? '退出全屏' : '全屏'}}
            <i
              :class="isFullScreen() ? 'el-icon-crop' : 'el-icon-full-screen'"
              style="margin-left: 10px"
             
            ></i>
            <!-- <i
              class="blq-icon-shezhi02"
              style="margin-left: 10px"
              @click="showSetting"
            ></i> -->
          </div>
        </div>
        <!-- 头部 e-->
        <!-- 内容  s-->
        <!-- <indexPage></indexPage> -->
        <router-view></router-view>
        <!-- 内容 e -->
      </div>
    </div>
    <Setting ref="setting" />
  </ScaleScreen>
  <!-- </div> -->
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
import { screenFormatTime } from "@/utils/index.js";
import Setting from "./setting.vue";
import ScaleScreen from "./components/scale-screen/scale-screen.vue";
import indexPage from "./indexs/index.vue";
export default {
  components: { Setting, ScaleScreen, indexPage },
  data() {
    return {
      timing: null,
      loading: true,
      dateDay: null,
      dateYear: null,
      dateWeek: null,
      weekday: ["周日", "周一", "周二", "周三", "周四", "周五", "周六"],
      screenTitle: ""
    };
  },
  filters: {
    numsFilter(msg) {
      return msg || 0;
    },
  },
  computed: {},
  created() {},
  mounted() {
    this.timeFn();
    this.cancelLoading();
    currentGET("config")
      .then((res) => {
        this.screenTitle = res.data.config.data_screen_title || '商城可视化数据大屏'
      })
    .catch((err) => {
      this.pageflag = false;
      this.$message({
        text: err,
        type: "warning",
      });
    });
  },
  beforeDestroy() {
    clearInterval(this.timing);
  },
  methods: {
    fullScreen() {
      this.loading = true;
      if (this.isFullScreen()) {
        this.exitFullscreen();
      } else {
        let el = document.getElementById("app");
        this.full(el);
      }
      setTimeout(() => {
        this.loading = false;
      }, 1000);
    },
    full(ele) {
      if (ele.requestFullscreen) {
        ele.requestFullscreen();
      } else if (ele.mozRequestFullScreen) {
        ele.mozRequestFullScreen();
      } else if (ele.webkitRequestFullscreen) {
        ele.webkitRequestFullscreen();
      } else if (ele.msRequestFullscreen) {
        ele.msRequestFullscreen();
      }
    },
    exitFullscreen() {
      if (document.exitFullScreen) {
        document.exitFullScreen();
      } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
      } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
      } else if (element.msExitFullscreen) {
        element.msExitFullscreen();
      }
    },
    isFullScreen() {
      return !!(
        document.mozFullScreen ||
        document.webkitIsFullScreen ||
        document.webkitFullScreen ||
        document.msFullScreen
      );
    },
    showSetting() {
      this.$refs.setting.init();
    },
    timeFn() {
      this.timing = setInterval(() => {
        this.dateDay = screenFormatTime(new Date(), "HH: mm: ss");
        this.dateYear = screenFormatTime(new Date(), "yyyy-MM-dd");
        this.dateWeek = this.weekday[new Date().getDay()];
      }, 1000);
    },
    cancelLoading() {
      let timer = setTimeout(() => {
        this.loading = false;
        clearTimeout(timer);
      }, 500);
    },
  },
};
</script>

<style lang="scss">
@import "./home.scss";
</style>

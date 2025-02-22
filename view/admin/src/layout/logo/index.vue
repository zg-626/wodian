<template>
  <div
    class="layout-logo"
    v-if="$store.state.themeConfig.themeConfig.layout !== 'columns' && !$store.state.themeConfig.themeConfig.isCollapse"
    @click="onThemeConfigChange"
  >
    <img v-if="maxLogo" class="layout-logo-medium-img" :src="maxLogo" />
  </div>
  <div class="layout-logo-size" v-else @click="onThemeConfigChange">
    <img v-if="minLogo" class="layout-logo-size-img" :src="minLogo" />
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
import Cookies from 'js-cookie'
import { roterPre } from "@/settings";
export default {
  name: 'layoutLogo',
  data() {
    return {
      roterPre: roterPre,
      maxLogo: JSON.parse(Cookies.get('MerInfo')).menu_logo,
      minLogo: JSON.parse(Cookies.get('MerInfo')).menu_slogo
    };
  },
  computed: {
    // 获取布局配置信息
    getThemeConfig() {
      return this.$store.state.themeConfig.themeConfig;
    },
    // 设置 logo 是否显示
    setShowLogo() {
      let { isCollapse, layout } = this.$store.state.themeConfig.themeConfig;
      return !isCollapse || layout === 'classic' || document.body.clientWidth < 1000;
    },
  },
  mounted() {

  },
  methods: {
    // logo 点击实现菜单展开/收起
    onThemeConfigChange() {
      // if (
      //   this.$store.state.themeConfig.themeConfig.layout == 'columns' &&
      //   !this.$store.state.user.childMenuList.length &&
      //   this.$store.state.themeConfig.themeConfig.isCollapse
      // )
      //   return;
      // if (this.$store.state.themeConfig.themeConfig.layout === 'transverse' || this.$store.state.themeConfig.themeConfig.layout === 'classic') return false;
      // this.$store.state.themeConfig.themeConfig.isCollapse = !this.$store.state.themeConfig.themeConfig.isCollapse;
      this.$router.push(
        `${roterPre}/dashboard`
      );
    },
   
  },
};
</script>

<style scoped lang="scss">
.layout-logo {
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--prev-color-primary);
  font-size: 16px;
  cursor: pointer;
  animation: logoAnimation 0.3s ease-in-out;
  height: 50px;
  line-height: 50px;
  width: 180px;
  &:hover {
    span {
      opacity: 0.9;
    }
  }
  &-medium-img {
    height: 32px;
  }
}
.layout-logo-size {
  display: flex;
  cursor: pointer;
  margin: auto;
  height: 50px;
  &-img {
    width: 50px;
    height: auto;
    margin: auto;
    animation: logoAnimation 0.3s ease-in-out;
  }
}
</style>

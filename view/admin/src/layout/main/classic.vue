<template>
  <el-container class="layout-container flex-center">
    <Headers />
    <el-container class="layout-mian-height-50">
      <Asides />
      <div class="flex-center layout-backtop">
        <TagsView v-if="getThemeConfig.isTagsview" />
        <Mains />
      </div>
    </el-container>
    <el-backtop target=".layout-backtop .el-main .el-scrollbar__wrap"></el-backtop>
  </el-container>
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
import Asides from '@/layout/component/aside.vue';
import Headers from '@/layout/component/header.vue';
import Mains from '@/layout/component/main.vue';
import TagsView from '@/layout/navBars/tagsView/tagsView.vue';
export default {
  name: 'layoutClassic',
  components: { Asides, Headers, Mains, TagsView },
  computed: {
    // 获取布局配置信息
    getThemeConfig() {
      return this.$store.state.themeConfig.themeConfig;
    },
  },
};
</script>

<template>
  <div class="layout-navbars-container">
    <BreadcrumbIndex />
    <TagsView v-if="setShowTagsView" />
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
import BreadcrumbIndex from '@/layout/navBars/breadcrumb/index.vue';
import TagsView from '@/layout/navBars/tagsView/tagsView.vue';
export default {
  name: 'layoutNavBars',
  components: { BreadcrumbIndex, TagsView },
  data() {
    return {};
  },
  computed: {
    // 设置是否显示 tagsView
    setShowTagsView() {
      let { layout, isTagsview } = this.$store.state.themeConfig.themeConfig;
      return layout !== 'classic' && isTagsview;
    },
  },
};
</script>

<style scoped lang="scss">
.layout-navbars-container {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100%;
  
}
</style>

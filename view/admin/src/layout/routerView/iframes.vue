<template>
  <div>
    <div class="layout-view-bg-white flex h100" v-loading="iframeLoading">
      <iframe :src="meta.isLink" frameborder="0" height="100%" width="100%" id="iframe"></iframe>
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
export default {
  name: 'layoutIfameView',
  props: {
    meta: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      iframeLoading: true,
    };
  },
  created() {
    this.bus.$on('onTagsViewRefreshRouterView', (path) => {
      if (this.$route.path !== path) return false;
      this.$emit('getCurrentRouteMeta');
    });
  },
  mounted() {
    this.initIframeLoad();
  },
  methods: {
    // 初始化页面加载 loading
    initIframeLoad() {
      this.$nextTick(() => {
        this.iframeLoading = true;
        const iframe = document.getElementById('iframe');
        if (!iframe) return false;
        iframe.onload = () => {
          this.iframeLoading = false;
        };
      });
    },
  },
};
</script>

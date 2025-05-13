<template>
  <div class="layout-footer mt15">
    <div class="layout-footer-warp">
      <iCopyright />
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
import iCopyright from '@/components/copyright';
export default {
  components: { iCopyright },
  name: 'layoutFooter',
  data() {
    return {};
  },
};
</script>

<style scoped lang="scss">
.layout-footer {
  width: 100%;
  display: flex;
  &-warp {
    margin: auto;
    color: var(--prev-color-text-secondary);
    text-align: center;
  }
}
</style>

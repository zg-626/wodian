<template>
  <el-container class="layout-container">
    <ColumnsAside />
    <div class="layout-columns-warp">
      <Asides />
      <el-container class="flex-center layout-backtop">
        <Headers v-if="isFixedHeader" />
        <el-scrollbar>
          <Headers v-if="!isFixedHeader" />
          <Mains />
        </el-scrollbar>
      </el-container>
    </div>
    <el-backtop target=".layout-backtop .el-scrollbar__wrap"></el-backtop>
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
import ColumnsAside from '@/layout/component/columnsAside.vue';
export default {
  name: 'layoutColumns',
  components: { Asides, Headers, Mains, ColumnsAside },
  computed: {
    // 是否开启固定 header
    isFixedHeader() {
      return this.$store.state.themeConfig.themeConfig.isFixedHeader;
    },
  },
};
</script>

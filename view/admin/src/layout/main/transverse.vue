<template>
  <el-container class="layout-container flex-center layout-backtop">
    <Headers />
    <Mains />
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
import Headers from '@/layout/component/header.vue';
import Mains from '@/layout/component/main.vue';
export default {
  name: 'layoutTransverse',
  components: { Headers, Mains },
};
</script>

<template>
  <div class='kong'>
      <slot></slot>
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
  components: {},
  data() {
    return {
    
    };
  },
  props:{
    data:{
      type:Array,
      default:()=>[]
    }
  },
  computed: {},
  methods: {
    init(){
    },
  },
  created() {
  
  },
  mounted() {
  
  },
  beforeDestroy() { 
  },
}
</script>

<style lang='scss' scoped>
.kong{
    width: 100%;
    height: 100%;
}
</style>